<?php declare(strict_types=1);

namespace ObMailchimpSync\Subscriber;

use ObMailchimpSync\Service\RecipientSynchronizer;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SystemConfig\Event\SystemConfigMultipleChangedEvent;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Schalter "Jetzt prüfen und alle übertragen" in der Plugin-Konfiguration:
 * beim Speichern Verbindung testen, Komplett-Abgleich ausführen und das Ergebnis
 * ins Feld "Letztes Ergebnis" schreiben. Danach wird der Schalter zurückgesetzt.
 */
class ConfigSavedSubscriber implements EventSubscriberInterface
{
    private const SYNC_NOW = 'ObMailchimpSync.config.syncNow';

    private bool $running = false;

    public function __construct(
        private readonly RecipientSynchronizer $synchronizer,
        private readonly SystemConfigService $systemConfig,
        private readonly EntityRepository $salesChannelRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SystemConfigMultipleChangedEvent::class => 'onConfigSaved',
        ];
    }

    public function onConfigSaved(SystemConfigMultipleChangedEvent $event): void
    {
        $config = $event->getConfig();

        if ($this->running || !\array_key_exists(self::SYNC_NOW, $config) || $config[self::SYNC_NOW] !== true) {
            return;
        }

        $this->running = true;
        $scope = $event->getSalesChannelId();
        $context = Context::createCLIContext();

        try {
            if ($scope !== null) {
                $report = $this->synchronizer->runDiagnostic($scope, $context);
            } else {
                // "Alle Verkaufskanäle": jeden Verkaufskanal einzeln prüfen
                $parts = [];
                /** @var list<string> $ids */
                $ids = $this->salesChannelRepository->searchIds(new Criteria(), $context)->getIds();
                foreach ($ids as $id) {
                    $parts[] = '[' . $this->channelName($id, $context) . '] ' . $this->synchronizer->runDiagnostic($id, $context);
                }
                $report = implode(' ', $parts);
            }
        } catch (\Throwable $e) {
            $report = 'Unerwarteter Fehler: ' . $e->getMessage();
        }

        $this->systemConfig->set(self::SYNC_NOW, false, $scope);
        $this->synchronizer->writeResult($scope, $report);
        $this->running = false;
    }

    private function channelName(string $id, Context $context): string
    {
        $channel = $this->salesChannelRepository->search(new Criteria([$id]), $context)->first();

        return $channel?->getTranslation('name') ?? $id;
    }
}
