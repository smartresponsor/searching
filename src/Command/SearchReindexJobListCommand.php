<?php

declare(strict_types=1);

namespace App\Searching\Command;

use App\Searching\Service\Serialization\SearchReindexJobSerializer;
use App\Searching\ServiceInterface\Indexing\SearchReindexJobReaderInterface;
use App\Searching\Value\Indexing\SearchReindexJobCriteria;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'searching:reindex-job:list', description: 'List Searching reindex jobs.')]
final class SearchReindexJobListCommand extends Command
{
    public function __construct(
        private readonly SearchReindexJobReaderInterface $reader,
        private readonly SearchReindexJobSerializer $serializer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Maximum number of jobs to list.', '50')
            ->addOption('offset', null, InputOption::VALUE_REQUIRED, 'Result offset.', '0')
            ->addOption('job', null, InputOption::VALUE_REQUIRED, 'Filter by job key.')
            ->addOption('component', null, InputOption::VALUE_REQUIRED, 'Filter by component.')
            ->addOption('resource', null, InputOption::VALUE_REQUIRED, 'Filter by resource type.')
            ->addOption('status', null, InputOption::VALUE_REQUIRED, 'Filter by status.')
            ->addOption('from', null, InputOption::VALUE_REQUIRED, 'Filter jobs created from this date/time.')
            ->addOption('to', null, InputOption::VALUE_REQUIRED, 'Filter jobs created to this date/time.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $criteria = SearchReindexJobCriteria::fromArray([
            'limit' => $input->getOption('limit'),
            'offset' => $input->getOption('offset'),
            'jobKey' => $input->getOption('job'),
            'component' => $input->getOption('component'),
            'resource' => $input->getOption('resource'),
            'status' => $input->getOption('status'),
            'from' => $input->getOption('from'),
            'to' => $input->getOption('to'),
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
