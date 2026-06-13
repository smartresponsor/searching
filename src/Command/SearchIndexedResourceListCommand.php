<?php

declare(strict_types=1);

namespace App\Searching\Command;

use App\Searching\Service\Serialization\SearchIndexedResourceSerializer;
use App\Searching\ServiceInterface\Indexing\SearchIndexedResourceReaderInterface;
use App\Searching\Value\Indexing\SearchIndexedResourceCriteria;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'searching:indexed-resource:list', description: 'List indexed resources tracked by Searching.')]
final class SearchIndexedResourceListCommand extends Command
{
    public function __construct(
        private readonly SearchIndexedResourceReaderInterface $reader,
        private readonly SearchIndexedResourceSerializer $serializer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Maximum number of indexed resources to list.', '50')
            ->addOption('offset', null, InputOption::VALUE_REQUIRED, 'Result offset.', '0')
            ->addOption('component', null, InputOption::VALUE_REQUIRED, 'Filter by component.')
            ->addOption('resource', null, InputOption::VALUE_REQUIRED, 'Filter by resource type.')
            ->addOption('resource-id', null, InputOption::VALUE_REQUIRED, 'Filter by source resource id.')
            ->addOption('status', null, InputOption::VALUE_REQUIRED, 'Filter by ledger status.')
            ->addOption('stale', null, InputOption::VALUE_REQUIRED, 'Filter stale entries: true or false.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $criteria = SearchIndexedResourceCriteria::fromArray([
            'limit' => $input->getOption('limit'),
            'offset' => $input->getOption('offset'),
            'component' => $input->getOption('component'),
            'resource' => $input->getOption('resource'),
            'resource_id' => $input->getOption('resource-id'),
            'status' => $input->getOption('status'),
            'stale' => $input->getOption('stale'),
        ], 50);

        $payload = [
            'items' => $this->serializer->serializeList($this->reader->list($criteria)),
            'total' => $this->reader->count($criteria),
            'limit' => $criteria->limit,
            'offset' => $criteria->offset,
        ];

        $output->writeln((string) json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return Command::SUCCESS;
    }
}
