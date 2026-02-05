<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\MamTaxiClient;
use App\Service\OrderImporter;
use App\Service\OrderUpdatesTracker;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:import-orders', description: 'Imports orders from MamTaxi in batches')]
class ImportOrdersCommand extends Command
{
    public function __construct(
        private readonly MamTaxiClient $mamTaxiClient,
        private readonly OrderImporter $orderImporter,
        private readonly OrderUpdatesTracker $updatesTracker,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('how-many', null, InputOption::VALUE_REQUIRED, 'How many orders to import', 5000)
            ->addOption('batch-size', null, InputOption::VALUE_REQUIRED, 'Batch size per API request', 250)
            ->addOption('concurrency', null, InputOption::VALUE_REQUIRED, 'Concurrent order details requests', 25);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $howMany = max(1, (int) $input->getOption('how-many'));
        $batchSize = max(1, (int) $input->getOption('batch-size'));
        $concurrency = max(1, (int) $input->getOption('concurrency'));
        $imported = 0;

        $io->text("Starting import: {$howMany} orders (batch {$batchSize}, concurrency {$concurrency})");

        try {
            for ($start = 0; $start < $howMany; $start += $batchSize) {
                $limit = min($batchSize, $howMany - $start);
                $orders = $this->mamTaxiClient->fetchOrdersWithDetails($limit, $start, $concurrency);
                if ([] === $orders) {
                    break;
                }

                $this->orderImporter->importFromArray($orders);
                $this->updatesTracker->touch();
                $imported += count($orders);

                $io->text("Imported {$imported}/{$howMany}");

                if (count($orders) < $limit) {
                    break;
                }
            }
        } catch (\Throwable $exception) {
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }

        $io->success("Import complete. Imported {$imported} orders.");

        return Command::SUCCESS;
    }
}
