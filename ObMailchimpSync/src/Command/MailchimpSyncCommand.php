<?php declare(strict_types=1);

namespace ObMailchimpSync\Command;

use ObMailchimpSync\Service\RecipientSynchronizer;
use Shopware\Core\Framework\Context;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'ob:mailchimp:sync', description: 'Überträgt alle Newsletter-Empfänger an Mailchimp')]
class MailchimpSyncCommand extends Command
{
    public function __construct(private readonly RecipientSynchronizer $synchronizer)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $results = $this->synchronizer->syncAll(
            Context::createCLIContext(),
            static function (string $channelId, array $result) use ($io): void {
                $io->writeln(\sprintf(
                    'Verkaufskanal %s: %d neu, %d schon vorhanden (unverändert), %d abgemeldet, %d Fehler',
                    $channelId,
                    $result['created'],
                    $result['existing'],
                    $result['unsubscribed'],
                    \count($result['errors'])
                ));

                foreach ($result['errors'] as $error) {
                    $io->writeln('  - ' . $error);
                }
            }
        );

        if ($results === []) {
            $io->warning('Kein Verkaufskanal mit aktiver Mailchimp-Synchronisation (oder keine Empfänger).');
        } else {
            $io->success('Abgleich abgeschlossen.');
        }

        return Command::SUCCESS;
    }
}
