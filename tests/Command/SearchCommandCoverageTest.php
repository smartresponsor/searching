<?php

declare(strict_types=1);

namespace App\Searching\Tests\Command;

use App\Searching\Command\SearchDocumentRemoveCommand;
use App\Searching\Command\SearchHealthCommand;
use App\Searching\Command\SearchIndexedResourceListCommand;
use App\Searching\Command\SearchIndexLifecycleCommand;
use App\Searching\Command\SearchIndexListCommand;
use App\Searching\Command\SearchIndexRebuildCommand;
use App\Searching\Command\SearchProviderStatusCommand;
use App\Searching\Command\SearchQueryLogListCommand;
use App\Searching\Command\SearchReindexEnqueueCommand;
use App\Searching\Command\SearchReindexJobListCommand;
use App\Searching\Contract\Health\SearchHealthCheckerInterface;
use App\Searching\Contract\Indexing\SearchIncrementalIndexerInterface;
use App\Searching\Contract\Indexing\SearchIndexedResourceReaderInterface;
use App\Searching\Contract\Indexing\SearchIndexLifecycleManagerInterface;
use App\Searching\Contract\Indexing\SearchIndexReaderInterface;
use App\Searching\Contract\Indexing\SearchReindexCoordinatorInterface;
use App\Searching\Contract\Indexing\SearchReindexDispatcherInterface;
use App\Searching\Contract\Indexing\SearchReindexJobReaderInterface;
use App\Searching\Contract\Provider\SearchProviderInterface;
use App\Searching\Contract\Query\SearchQueryLogReaderInterface;
use App\Searching\Service\Serialization\SearchHealthReportSerializer;
use App\Searching\Service\Serialization\SearchIndexedResourceSerializer;
use App\Searching\Service\Serialization\SearchIndexSerializer;
use App\Searching\Service\Serialization\SearchQueryLogSerializer;
use App\Searching\Service\Serialization\SearchReindexJobSerializer;
use App\Searching\Value\Health\SearchHealthReport;
use App\Searching\Value\Indexing\SearchDocumentChangeResult;
use App\Searching\Value\Indexing\SearchReindexDispatchResult;
use App\Searching\Value\Indexing\SearchReindexResult;
use App\Searching\Value\Provider\SearchIndexLifecycleResult;
use App\Searching\Value\Provider\SearchProviderStatus;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class SearchCommandCoverageTest extends TestCase
{
    public function testMutationAndStatusCommands(): void
    {
        $indexer = $this->createMock(SearchIncrementalIndexerInterface::class);
        $indexer->expects(self::once())->method('removeResource')->with('ordering', 'order', '42', 'command')
            ->willReturn(SearchDocumentChangeResult::removed('ordering', 'order', '42'));
        $remove = new CommandTester(new SearchDocumentRemoveCommand($indexer));
        self::assertSame(Command::SUCCESS, $remove->execute(['component' => 'ordering', 'resource' => 'order', 'id' => '42']));
        self::assertStringContainsString('removed: ordering/order/42', $remove->getDisplay());

        $checker = $this->createMock(SearchHealthCheckerInterface::class);
        $checker->method('check')->willReturnOnConsecutiveCalls(
            new SearchHealthReport('healthy', [], new \DateTimeImmutable('2026-09-16T12:00:00+00:00')),
            new SearchHealthReport('unhealthy', [], new \DateTimeImmutable('2026-09-16T12:01:00+00:00')),
        );
        $health = new CommandTester(new SearchHealthCommand($checker, new SearchHealthReportSerializer()));
        self::assertSame(Command::SUCCESS, $health->execute([]));
        self::assertSame(Command::FAILURE, $health->execute([]));

        $provider = $this->createMock(SearchProviderInterface::class);
        $provider->method('getStatus')->willReturn(new SearchProviderStatus('null', true, 'available'));
        $status = new CommandTester(new SearchProviderStatusCommand($provider));
        self::assertSame(Command::SUCCESS, $status->execute([]));
        self::assertStringContainsString('null: available', $status->getDisplay());
    }

    public function testLifecycleCommandCoversPublicOperationModes(): void
    {
        $manager = $this->createMock(SearchIndexLifecycleManagerInterface::class);
        $manager->method('ensureForAllProviders')->willReturn([
            'null' => new SearchIndexLifecycleResult('null', 'ordering_order', 'ensure', 'ready', false),
        ]);
        $manager->method('ensure')->willReturn(new SearchIndexLifecycleResult('elastic', 'ordering_order', 'ensure', 'ready', true));
        $manager->method('delete')->willReturn(new SearchIndexLifecycleResult('elastic', 'ordering_order', 'delete', 'deleted', true));
        $tester = new CommandTester(new SearchIndexLifecycleCommand($manager));

        self::assertSame(Command::INVALID, $tester->execute(['operation' => 'rotate', 'component' => 'ordering', 'resource' => 'order']));
        self::assertSame(Command::INVALID, $tester->execute(['operation' => 'delete', 'component' => 'ordering', 'resource' => 'order']));
        self::assertSame(Command::SUCCESS, $tester->execute(['operation' => 'ensure', 'component' => 'ordering', 'resource' => 'order']));
        self::assertSame(Command::SUCCESS, $tester->execute(['operation' => 'ensure', 'component' => 'ordering', 'resource' => 'order', '--provider' => 'elastic']));
        self::assertSame(Command::SUCCESS, $tester->execute(['operation' => 'delete', 'component' => 'ordering', 'resource' => 'order', '--provider' => 'elastic']));
    }

    public function testReadListCommandsSerializeEmptyCollections(): void
    {
        $indexReader = $this->createMock(SearchIndexReaderInterface::class);
        $indexReader->method('list')->willReturn([]);
        $indexReader->method('count')->willReturn(0);
        $index = new CommandTester(new SearchIndexListCommand($indexReader, new SearchIndexSerializer()));
        self::assertSame(Command::SUCCESS, $index->execute(['--limit' => '7', '--offset' => '2', '--provider' => 'elastic', '--enabled' => 'true']));
        self::assertStringContainsString('"limit": 7', $index->getDisplay());

        $resourceReader = $this->createMock(SearchIndexedResourceReaderInterface::class);
        $resourceReader->method('list')->willReturn([]);
        $resourceReader->method('count')->willReturn(0);
        $resource = new CommandTester(new SearchIndexedResourceListCommand($resourceReader, new SearchIndexedResourceSerializer()));
        self::assertSame(Command::SUCCESS, $resource->execute(['--limit' => '8', '--component' => 'ordering', '--stale' => 'true']));

        $queryReader = $this->createMock(SearchQueryLogReaderInterface::class);
        $queryReader->method('recent')->willReturn([]);
        $queryReader->method('count')->willReturn(0);
        $query = new CommandTester(new SearchQueryLogListCommand($queryReader, new SearchQueryLogSerializer()));
        self::assertSame(Command::SUCCESS, $query->execute(['--query' => 'needle', '--successful' => 'false']));

        $jobReader = $this->createMock(SearchReindexJobReaderInterface::class);
        $jobReader->method('list')->willReturn([]);
        $jobReader->method('count')->willReturn(0);
        $jobs = new CommandTester(new SearchReindexJobListCommand($jobReader, new SearchReindexJobSerializer()));
        self::assertSame(Command::SUCCESS, $jobs->execute(['--job' => 'job-1', '--status' => 'done']));
    }

    public function testRebuildAndEnqueueCommandsNormalizeOptions(): void
    {
        $coordinator = $this->createMock(SearchReindexCoordinatorInterface::class);
        $coordinator->method('reindex')->willReturnOnConsecutiveCalls(
            new SearchReindexResult('job-ok', 2, 10),
            new SearchReindexResult('job-fail', 1, 3, 1, ['provider failed']),
        );
        $rebuild = new CommandTester(new SearchIndexRebuildCommand($coordinator));
        self::assertSame(Command::SUCCESS, $rebuild->execute(['--component' => ' ', '--resource' => '']));
        self::assertSame(Command::FAILURE, $rebuild->execute([
            '--component' => ' ordering ',
            '--resource' => ' order ',
            '--since' => '2026-09-15T12:00:00+00:00',
        ]));
        self::assertStringContainsString('provider failed', $rebuild->getDisplay());

        $dispatcher = $this->createMock(SearchReindexDispatcherInterface::class);
        $dispatcher->method('dispatch')->willReturn(new SearchReindexDispatchResult('job-queued', 'messenger', true));
        $enqueue = new CommandTester(new SearchReindexEnqueueCommand($dispatcher));
        self::assertSame(Command::SUCCESS, $enqueue->execute([
            '--component' => ' ordering ',
            '--resource' => ' ',
            '--since' => '2026-09-15T12:00:00+00:00',
            '--requested-by' => ' user-1 ',
        ]));
        self::assertStringContainsString('Queued: yes', $enqueue->getDisplay());
    }
}
