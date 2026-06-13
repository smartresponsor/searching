<?php

declare(strict_types=1);

namespace App\Searching\Command;

use App\Searching\ServiceInterface\Indexing\SearchIndexLifecycleManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'searching:index:lifecycle', description: 'Prepare or remove managed search indexes.')]
final class SearchIndexLifecycleCommand extends Command
{
    public function __construct(private readonly SearchIndexLifecycleManagerInterface $lifecycleManager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('operation', InputArgument::REQUIRED, 'Lifecycle operation: ensure or delete.')
            ->addArgument('component', InputArgument::REQUIRED, 'Searchable component nameEntity.')
            ->addArgument('resource', InputArgument::REQUIRED, 'Searchable resource type.')
            ->addOption('provider', null, InputOption::VALUE_OPTIONAL, 'Provider nameEntity. Omit to run ensure for all providers.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $operation = (string) $input->getArgument('operation');
        $component = (string) $input->getArgument('component');
        $resource = (string) $input->getArgument('resource');
        $provider = $input->getOption('provider');

        if (!in_array($operation, ['ensure', 'delete'], true)) {
            $output->writeln('<error>Operation must be either ensure or delete.</error>');

            return Command::INVALID;
        }

        if (null !== $provider && !is_string($provider)) {
            $output->writeln('<error>Provider option must be a string.</error>');

            return Command::INVALID;
        }

        $results = [];
        if ('ensure' === $operation && (null === $provider || '' === $provider)) {
            $results = $this->lifecycleManager->ensureForAllProviders($component, $resource);
        } elseif ('ensure' === $operation) {
            $results[$provider] = $this->lifecycleManager->ensure($provider, $component, $resource);
        } else {
            if (null === $provider || '' === $provider) {
                $output->writeln('<error>Delete operation requires --provider.</error>');

                return Command::INVALID;
            }
            $results[$provider] = $this->lifecycleManager->delete($provider, $component, $resource);
        }

        foreach ($results as $providerName => $result) {
            $output->writeln(sprintf(
                '%s %s %s status=%s changed=%s',
                $providerName,
                $result->operation,
                $result->indexName,
                $result->status,
                $result->changed ? 'yes' : 'no',
            ));
        }

        return Command::SUCCESS;
    }
}
