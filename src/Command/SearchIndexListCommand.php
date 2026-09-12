<?php

declare(strict_types=1);

namespace App\Searching\Command;

use App\Searching\Contract\Indexing\SearchIndexReaderInterface;
use App\Searching\Service\Serialization\SearchIndexSerializer;
use App\Searching\Value\Indexing\SearchIndexCriteria;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'searching:index:list', description: 'List registered Searching index definitions.')]
final class SearchIndexListCommand extends Command
{
    public function __construct(
        private readonly SearchIndexReaderInterface $reader,
        private readonly SearchIndexSerializer $serializer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Maximum number of indexes to list.', '50')
            ->addOption('offset', null, InputOption::VALUE_REQUIRED, 'Result offset.', '0')
            ->addOption('provider', null, InputOption::VALUE_REQUIRED, 'Filter by provider.')
            ->addOption('component', null, InputOption::VALUE_REQUIRED, 'Filter by component.')
            ->addOption('resource', null, InputOption::VALUE_REQUIRED, 'Filter by resource type.')
            ->addOption('enabled', null, InputOption::VALUE_REQUIRED, 'Filter enabled indexes: true or false.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $criteria = SearchIndexCriteria::fromArray([
            'limit' => $input->getOption('limit'),
            'offset' => $input->getOption('offset'),
            'provider' => $input->getOption('provider'),
            'component' => $input->getOption('component'),
            'resource' => $input->getOption('resource'),
            'enabled' => $input->getOption('enabled'),
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
