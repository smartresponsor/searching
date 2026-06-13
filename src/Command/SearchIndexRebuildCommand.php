<?php

declare(strict_types=1);

namespace App\Searching\Command;

use App\Searching\ServiceInterface\Indexing\SearchReindexCoordinatorInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'searching:index:rebuild')]
final class SearchIndexRebuildCommand extends Command
{
    public function __construct(private readonly SearchReindexCoordinatorInterface $coordinator)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('component', null, InputOption::VALUE_OPTIONAL, 'Limit reindex to one producer component.')
            ->addOption('resource', null, InputOption::VALUE_OPTIONAL, 'Limit reindex to one resource type.')
            ->addOption('since', null, InputOption::VALUE_OPTIONAL, 'Only ask producers for documents changed since this date/time.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $changedSince = null;
        $since = $input->getOption('since');

        if (is_string($since) && '' !== $since) {
            $changedSince = new \DateTimeImmutable($since);
        }

        $result = $this->coordinator->reindex(
            component: $this->normalizeOption($input->getOption('component')),
            resourceType: $this->normalizeOption($input->getOption('resource')),
            changedSince: $changedSince,
        );

        $output->writeln('Search reindex job: '.$result->jobId);
        $output->writeln('Providers: '.$result->providerCount);
        $output->writeln('Documents: '.$result->documentCount);
        $output->writeln('Failures: '.$result->failedCount);

        foreach ($result->errors as $error) {
            $output->writeln('<error>'.$error.'</error>');
        }

        return $result->isSuccessful() ? Command::SUCCESS : Command::FAILURE;
    }

    private function normalizeOption(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return '' !== $value ? $value : null;
    }
}
