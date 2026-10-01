<?php declare(strict_types=1);

namespace ObMailchimpSync\Service;

use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Newsletter\Aggregate\NewsletterRecipient\NewsletterRecipientCollection;
use Shopware\Core\Content\Newsletter\Aggregate\NewsletterRecipient\NewsletterRecipientEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * Überträgt Newsletter-Empfänger nach Mailchimp.
 *
 * Shopware-Status -> Mailchimp:
 *  - optIn / direct (bestätigt)  -> neuer Kontakt "subscribed"; BESTEHENDE Kontakte bleiben unverändert
 *                                   (wer sich in Mailchimp abgemeldet hat, wird nie wieder angemeldet)
 *  - optOut (abgemeldet)         -> vorhandener Kontakt wird "unsubscribed" (wenn aktiviert), nie neu angelegt
 *  - notSet (noch nicht bestätigt) -> wird nicht übertragen (Double-Opt-in läuft in Shopware)
 *
 * Es wird nichts gelöscht – weder in Shopware noch in Mailchimp.
 */
class RecipientSynchronizer
{
    private const CONFIG = 'ObMailchimpSync.config.';

    /**
     * @param EntityRepository<NewsletterRecipientCollection> $recipientRepository
     */
    public function __construct(
        private readonly EntityRepository $recipientRepository,
        private readonly SystemConfigService $systemConfig,
        private readonly MailchimpClient $client,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Sofort-Abgleich einzelner Empfänger (nach Anmeldung, Bestätigung oder Abmeldung).
     *
     * @param list<string> $ids
     */
    public function syncRecipients(array $ids, Context $context): void
    {
        if ($ids === []) {
            return;
        }

        /** @var NewsletterRecipientCollection $recipients */
        $recipients = $this->recipientRepository->search(new Criteria($ids), $context)->getEntities();

        foreach ($recipients as $recipient) {
            $config = $this->getConfig($recipient->getSalesChannelId());
            if ($config === null) {
                continue;
            }

            $member = $this->toMember($recipient, $config);
            if ($member === null) {
                continue;
            }

            try {
                if ($member['status'] === 'subscribed') {
                    $this->client->addMember($config['apiKey'], $config['audienceId'], $member['email_address'], $member['merge_fields'] ?? []);
                } else {
                    $this->client->unsubscribeMember($config['apiKey'], $config['audienceId'], $member['email_address']);
                }
            } catch (\Throwable $e) {
                // Anmeldung im Shop darf nie an Mailchimp scheitern – nur protokollieren,
                // der nächtliche Komplett-Abgleich holt es nach.
                $this->logger->warning('Mailchimp-Sync fehlgeschlagen: ' . $e->getMessage(), [
                    'email' => $recipient->getEmail(),
                    'salesChannelId' => $recipient->getSalesChannelId(),
                ]);
            }
        }
    }

    /**
     * Komplett-Abgleich aller Empfänger aller aktiven Verkaufskanäle.
     *
     * @return array<string, array{created: int, existing: int, unsubscribed: int, errors: list<string>}> je Verkaufskanal-ID
     */
    public function syncAll(Context $context, ?callable $progress = null): array
    {
        $membersByChannel = [];
        $configs = [];
        $offset = 0;
        $limit = 500;

        do {
            $criteria = new Criteria();
            $criteria->addFilter(new EqualsAnyFilter('status', ['optIn', 'direct', 'optOut']));
            $criteria->addSorting(new FieldSorting('createdAt'));
            $criteria->setOffset($offset);
            $criteria->setLimit($limit);

            /** @var NewsletterRecipientCollection $recipients */
            $recipients = $this->recipientRepository->search($criteria, $context)->getEntities();

            foreach ($recipients as $recipient) {
                $channelId = $recipient->getSalesChannelId();

                if (!\array_key_exists($channelId, $configs)) {
                    $configs[$channelId] = $this->getConfig($channelId);
                }

                if ($configs[$channelId] === null) {
                    continue;
                }

                $member = $this->toMember($recipient, $configs[$channelId]);
                if ($member !== null) {
                    $membersByChannel[$channelId][] = $member;
                }
            }

            $offset += $limit;
        } while ($recipients->count() === $limit);

        $results = [];

        foreach ($membersByChannel as $channelId => $members) {
            $config = $configs[$channelId];

            $results[$channelId] = ['created' => 0, 'existing' => 0, 'unsubscribed' => 0, 'errors' => []];

            $subscribe = array_values(array_filter($members, static fn (array $m): bool => $m['status'] === 'subscribed'));
            $unsubscribe = array_values(array_filter($members, static fn (array $m): bool => $m['status'] === 'unsubscribed'));

            try {
                $batch = $this->client->batchAdd($config['apiKey'], $config['audienceId'], $subscribe);
                $results[$channelId]['created'] = $batch['created'];
                $results[$channelId]['existing'] = $batch['existing'];
                $results[$channelId]['errors'] = $batch['errors'];
            } catch (\Throwable $e) {
                $results[$channelId]['errors'][] = $e->getMessage();
            }

            foreach ($unsubscribe as $member) {
                try {
                    if ($this->client->unsubscribeMember($config['apiKey'], $config['audienceId'], $member['email_address'])) {
                        ++$results[$channelId]['unsubscribed'];
                    }
                } catch (\Throwable $e) {
                    $results[$channelId]['errors'][] = $member['email_address'] . ': ' . $e->getMessage();
                }
            }

            foreach ($results[$channelId]['errors'] as $error) {
                $this->logger->warning('Mailchimp-Komplettabgleich: ' . $error, ['salesChannelId' => $channelId]);
            }

            if ($progress !== null) {
                $progress($channelId, $results[$channelId]);
            }
        }

        return $results;
    }

    /**
     * @param array{apiKey: string, audienceId: string, syncNames: bool, syncUnsubscribe: bool} $config
     *
     * @return array{email_address: string, status: string, merge_fields?: array<string, string>}|null
     */
    private function toMember(NewsletterRecipientEntity $recipient, array $config): ?array
    {
        $status = match ($recipient->getStatus()) {
            'optIn', 'direct' => 'subscribed',
            'optOut' => $config['syncUnsubscribe'] ? 'unsubscribed' : null,
            default => null,
        };

        if ($status === null || trim($recipient->getEmail()) === '') {
            return null;
        }

        $member = [
            'email_address' => trim($recipient->getEmail()),
            'status' => $status,
        ];

        if ($config['syncNames']) {
            $mergeFields = array_filter([
                'FNAME' => trim((string) $recipient->getFirstName()),
                'LNAME' => trim((string) $recipient->getLastName()),
            ], static fn (string $value): bool => $value !== '');

            if ($mergeFields !== []) {
                $member['merge_fields'] = $mergeFields;
            }
        }

        return $member;
    }

    /**
     * @return array{apiKey: string, audienceId: string, syncNames: bool, syncUnsubscribe: bool}|null
     */
    private function getConfig(string $salesChannelId): ?array
    {
        if (!$this->systemConfig->getBool(self::CONFIG . 'enabled', $salesChannelId)) {
            return null;
        }

        $apiKey = trim($this->systemConfig->getString(self::CONFIG . 'apiKey', $salesChannelId));
        $audienceId = trim($this->systemConfig->getString(self::CONFIG . 'audienceId', $salesChannelId));

        if ($apiKey === '' || $audienceId === '') {
            return null;
        }

        return [
            'apiKey' => $apiKey,
            'audienceId' => $audienceId,
            'syncNames' => $this->systemConfig->get(self::CONFIG . 'syncNames', $salesChannelId) !== false,
            'syncUnsubscribe' => $this->systemConfig->get(self::CONFIG . 'syncUnsubscribe', $salesChannelId) !== false,
        ];
    }
}
