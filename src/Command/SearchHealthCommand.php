<?php

declare(strict_types=1);

namespace App\Searching\Command;

use App\Searching\Service\Serialization\SearchHealthReportSerializer;
use App\Searching\ServiceInterface\Health\SearchHealthCheckerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'searching:health', description: 'Print Searching provider, registry, lifecycle, freshness, and backlog health.')]
final class SearchHealthCommand extends Command
{
    public function __construct(
        private readonly SearchHealthCheckerInterface $healthChecker,
        private readonly SearchHealthReportSerializer $healthReportSerializer,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $report = $this->healthChecker->check();
        $output->writeln((string) json_encode(
            $this->healthReportSerializer->serializeReport($report),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES,
        ));

        return 'unhealthy' === $report->status ? Command::FAILURE : Command::SUCCESS;
    }
}
