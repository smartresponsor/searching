<?php

declare(strict_types=1);

namespace App\Searching\Command;

use App\Searching\Contract\Query\SearchQueryLogReaderInterface;
use App\Searching\Service\Serialization\SearchQueryLogSerializer;
use App\Searching\Value\Query\SearchQueryLogCriteria;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'searching:query-log:list', description: 'List recent Searching query log entries.')]
final class SearchQueryLogListCommand extends Command
{
    public function __construct(
        private readonly SearchQueryLogReaderInterface $reader,
        private readonly SearchQueryLogSerializer $serializer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Maximum number of query logs to list.', '25')
            ->addOption('offset', null, InputOption::VALUE_REQUIRED, 'Result offset.', '0')
            ->addOption('query', null, InputOption::VALUE_REQUIRED, 'Filter by query text.')
            ->addOption('provider', null, InputOption::VALUE_REQUIRED, 'Filter by provider nameEntity.')
            ->addOption('user-id', null, InputOption::VALUE_REQUIRED, 'Filter by user id.')
            ->addOption('vendor-id', null, InputOption::VALUE_REQUIRED, 'Filter by vendor id.')
            ->addOption('successful', null, InputOption::VALUE_REQUIRED, 'Filter by success flag: true or false.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $criteria = SearchQueryLogCriteria::fromArray([
            'limit' => $input->getOption('limit'),
            'offset' => $input->getOption('offset'),
            'query' => $input->getOption('query'),
            'provider' => $input->getOption('provider'),
            'user_id' => $input->getOption('user-id'),
            'vendor_id' => $input->getOption('vendor-id'),
            'successful' => $input->getOption('successful'),
        ], 25);

        $payload = [
            'items' => $this->serializer->serializeLogs($this->reader->recent($criteria)),
            'total' => $this->reader->count($criteria),
            'limit' => $criteria->limit,
            'offset' => $criteria->offset,
        ];

        $output->writeln((string) json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return Command::SUCCESS;
    }
}
