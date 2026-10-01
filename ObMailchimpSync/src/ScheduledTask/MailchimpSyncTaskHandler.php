<?php declare(strict_types=1);

namespace ObMailchimpSync\ScheduledTask;

use ObMailchimpSync\Service\RecipientSynchronizer;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: MailchimpSyncTask::class)]
final class MailchimpSyncTaskHandler extends ScheduledTaskHandler
{
    public function __construct(
        EntityRepository $scheduledTaskRepository,
        LoggerInterface $logger,
        private readonly RecipientSynchronizer $synchronizer,
    ) {
        parent::__construct($scheduledTaskRepository, $logger);
    }

    public function run(): void
    {
        $this->synchronizer->syncAll(Context::createCLIContext());
    }
}
