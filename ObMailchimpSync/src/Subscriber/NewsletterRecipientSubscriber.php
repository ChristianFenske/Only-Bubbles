<?php declare(strict_types=1);

namespace ObMailchimpSync\Subscriber;

use ObMailchimpSync\Service\RecipientSynchronizer;
use Shopware\Core\Content\Newsletter\NewsletterEvents;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Jede Änderung an einem Newsletter-Empfänger (Anmeldung, Bestätigung, Abmeldung,
 * Bearbeitung im Admin) wird sofort an Mailchimp übertragen.
 */
class NewsletterRecipientSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly RecipientSynchronizer $synchronizer)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            NewsletterEvents::NEWSLETTER_RECIPIENT_WRITTEN_EVENT => 'onRecipientWritten',
        ];
    }

    public function onRecipientWritten(EntityWrittenEvent $event): void
    {
        /** @var list<string> $ids */
        $ids = array_values(array_filter($event->getIds(), 'is_string'));

        try {
            $this->synchronizer->syncRecipients($ids, Context::createCLIContext());
        } catch (\Throwable) {
            // Niemals die Newsletter-Anmeldung im Shop blockieren.
        }
    }
}
