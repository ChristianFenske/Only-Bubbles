<?php declare(strict_types=1);

namespace ObMailchimpSync\ScheduledTask;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

/**
 * Täglicher Komplett-Abgleich: holt bestehende Empfänger nach und
 * korrigiert alles, was beim Sofort-Abgleich fehlgeschlagen ist.
 */
class MailchimpSyncTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'ob_mailchimp_sync.full_sync';
    }

    public static function getDefaultInterval(): int
    {
        return self::DAILY;
    }

    public static function shouldRescheduleOnFailure(): bool
    {
        return true;
    }
}
