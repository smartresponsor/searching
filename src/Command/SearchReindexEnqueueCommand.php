<?php

declare(strict_types=1);

namespace App\Searching\Command;

use App\Searching\ServiceInterface\Indexing\SearchReindexDispatcherInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'searching:index:enqueue')]
final class SearchReindexEnqueueCommand extends Command
{
    public function __construct(private readonly SearchReindexDispatcherInterface $dispatcher)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('component', null, InputOption::VALUE_OPTIONAL, 'Limit reindex to one producer component.')
            ->addOption('resource', null, InputOption::VALUE_OPTIONAL, 'Limit reindex to one resource type.')
            ->addOption('since', null, InputOption::VALUE_OPTIONAL, 'Only ask producers for documents changed since this date/time.')
            ->addOption('requested-by', null, InputOption::VALUE_OPTIONAL, 'Actor or system that requested the reindex.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $changedSince = null;
        $since = $input->getOption('since');

        if (is_string($since) && '' !== $since) {
            $changedSince = new \DateTimeImmutable($since);
        }

        $result = $this->dispatcher->dispatch(
            component: $this->normalizeOption($input->getOption('component')),
            resourceType: $this->normalizeOption($input->getOption('resource')),
            changedSince: $changedSince,
            requestedBy: $this->normalizeOption($input->getOption('requested-by')),
        );

        $output->writeln('Search reindex dispatch job: '.$result->jobId);
        $output->writeln('Mode: '.$result->mode);
        $output->writeln('Queued: '.($result->queued ? 'yes' : 'no'));

        return Command::SUCCESS;
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
