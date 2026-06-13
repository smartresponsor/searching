<?php

declare(strict_types=1);

namespace App\Searching\Command;

use App\Searching\ServiceInterface\Indexing\SearchIncrementalIndexerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'searching:document:remove', description: 'Remove a source resource from the active search provider index.')]
final class SearchDocumentRemoveCommand extends Command
{
    public function __construct(
        private readonly SearchIncrementalIndexerInterface $incrementalIndexer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('component', InputArgument::REQUIRED, 'Source component nameEntity.')
            ->addArgument('resource', InputArgument::REQUIRED, 'Source resource type.')
            ->addArgument('id', InputArgument::REQUIRED, 'Source resource id.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $result = $this->incrementalIndexer->removeResource(
            component: (string) $input->getArgument('component'),
            resourceType: (string) $input->getArgument('resource'),
            resourceId: (string) $input->getArgument('id'),
            changeReason: 'command',
        );

        $output->writeln(sprintf(
            'Search document %s: %s/%s/%s',
            $result->status,
            $result->component,
            $result->resourceType,
            $result->resourceId,
        ));

        return Command::SUCCESS;
    }
}
