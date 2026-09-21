<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service;

use App\Searching\Contract\Flow\SearchOperationLimiterInterface;
use App\Searching\Contract\Indexing\SearchIndexWriterInterface;
use App\Searching\Contract\Indexing\SearchReindexCoordinatorInterface;
use App\Searching\Entity\SearchIndexEntity;
use App\Searching\Service\Indexing\SearchIndexLifecycleRegistrySynchronizer;
use App\Searching\Service\Indexing\SearchSyncReindexDispatcher;
use App\Searching\Service\Query\SearchDoctrineQueryLogger;
use App\Searching\Service\Tuning\SearchDoctrineRelevanceProfileWriter;
use App\Searching\Service\Tuning\SearchDoctrineSynonymWriter;
use App\Searching\Value\Flow\SearchOperationLimitDecision;
use App\Searching\Value\Indexing\SearchReindexResult;
use App\Searching\Value\Provider\SearchIndexLifecycleResult;
use App\Searching\Value\Query\SearchQueryExecutionTrace;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

final class SearchPersistenceSupportCoverageTest extends TestCase
{
    public function testRelevanceProfileWriterCreatesUpdatesAndDeletesNormalizedProfile(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('persist');
        $entityManager->expects(self::once())->method('remove');
        $entityManager->expects(self::exactly(3))->method('flush');
        $writer = new SearchDoctrineRelevanceProfileWriter($entityManager);

        $profile = $writer->create([
            'nameEntity' => ' Primary ',
            'fieldWeights' => [' title ' => '2', 'score' => 1.5, 'ignored' => 'bad', '' => 3],
            'component' => ' ordering ',
            'resourceType' => ' order ',
            'enabled' => 'true',
        ]);
        self::assertSame('Primary', $profile->getName());
        self::assertSame(['title' => 2, 'score' => 1.5], $profile->getFieldWeights());
        self::assertSame('ordering', $profile->getComponent());

        self::assertSame($profile, $writer->update($profile, [
            'nameEntity' => 'Secondary',
            'field_weights' => ['body' => 3],
            'component' => '',
            'resource_type' => 'article',
            'enabled' => false,
        ]));
        self::assertSame('Secondary', $profile->getName());
        self::assertNull($profile->getComponent());
        self::assertFalse($profile->isEnabled());

        $writer->delete($profile);
    }

    public function testSynonymWriterCreatesUpdatesAndDeletesNormalizedSynonym(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('persist');
        $entityManager->expects(self::once())->method('remove');
        $entityManager->expects(self::exactly(3))->method('flush');
        $writer = new SearchDoctrineSynonymWriter($entityManager);

        $synonym = $writer->create([
            'source_term' => ' phone ',
            'targetTerms' => 'mobile, handset, mobile, ',
            'locale' => ' en ',
            'enabled' => '1',
        ]);
        self::assertSame('phone', $synonym->getSourceTerm());
        self::assertSame(['mobile', 'handset'], $synonym->getTargetTerms());
        self::assertSame('en', $synonym->getLocale());

        self::assertSame($synonym, $writer->update($synonym, [
            'sourceTerm' => 'telephone',
            'target_terms' => [' receiver ', '', 123],
            'locale' => '',
            'enabled' => false,
        ]));
        self::assertSame('telephone', $synonym->getSourceTerm());
        self::assertSame(['receiver', '123'], $synonym->getTargetTerms());
        self::assertNull($synonym->getLocale());

        $writer->delete($synonym);
    }

    public function testDoctrineQueryLoggerHonorsFlushMode(): void
    {
        $trace = new SearchQueryExecutionTrace(
            query: 'needle',
            userId: 'user-1',
            vendorId: 'vendor-1',
            providerName: 'null',
            providerTotal: 1,
            returnedTotal: 1,
            deniedCount: 0,
            durationMs: 1.5,
            executedAt: new \DateTimeImmutable('2026-09-16T12:00:00+00:00'),
        );
        $flushing = $this->createMock(EntityManagerInterface::class);
        $flushing->expects(self::once())->method('persist');
        $flushing->expects(self::once())->method('flush');
        (new SearchDoctrineQueryLogger($flushing, true))->log($trace);

        $deferred = $this->createMock(EntityManagerInterface::class);
        $deferred->expects(self::once())->method('persist');
        $deferred->expects(self::never())->method('flush');
        (new SearchDoctrineQueryLogger($deferred, false))->log($trace);
    }

    public function testIndexLifecycleSynchronizerMapsLifecycleMetadata(): void
    {
        $writer = $this->createMock(SearchIndexWriterInterface::class);
        $writer->expects(self::exactly(2))->method('upsert')->willReturnCallback(static function (array $payload): SearchIndexEntity {
            if ('deleted' === $payload['lifecycleStatus']) {
                self::assertFalse($payload['enabled']);
                self::assertSame('gone', $payload['lifecycleError']);
            } else {
                self::assertTrue($payload['enabled']);
                self::assertSame('{"code":42}', $payload['lifecycleError']);
            }

            return new SearchIndexEntity('index', 'elastic', 'idx', 'ordering', 'order');
        });
        $synchronizer = new SearchIndexLifecycleRegistrySynchronizer($writer);

        self::assertTrue($synchronizer->sync('ordering', 'order', new SearchIndexLifecycleResult(
            'elastic', 'idx', 'delete', 'deleted', true, ['reason' => 'gone'],
        ))->synced);
        self::assertTrue($synchronizer->sync('ordering', 'order', new SearchIndexLifecycleResult(
            'elastic', 'idx', 'ensure', 'created', true, ['error' => ['code' => 42]],
        ))->synced);
    }

    public function testSyncDispatcherCoversAllowedAndLimitedDecisions(): void
    {
        $coordinator = $this->createMock(SearchReindexCoordinatorInterface::class);
        $coordinator->expects(self::once())->method('reindex')->willReturn(new SearchReindexResult('job-1', 1, 2));
        $limiter = $this->createMock(SearchOperationLimiterInterface::class);
        $limiter->method('decide')->willReturnOnConsecutiveCalls(
            SearchOperationLimitDecision::allow(['bucket' => 'open']),
            SearchOperationLimitDecision::defer('busy', 5),
        );
        $dispatcher = new SearchSyncReindexDispatcher($coordinator, $limiter);

        $allowed = $dispatcher->dispatch('ordering', 'order', new \DateTimeImmutable('2026-09-15T12:00:00+00:00'), 'user-1');
        self::assertSame('job-1', $allowed->jobId);
        self::assertNotNull($allowed->syncResult);
        self::assertArrayHasKey('idempotency_key', $allowed->metadata);

        $limited = $dispatcher->dispatch('ordering', 'order', null, 'user-1');
        self::assertSame('limited', $limited->jobId);
        self::assertTrue($limited->metadata['limited']);
        $operationLimit = $limited->metadata['operation_limit'];
        self::assertIsArray($operationLimit);
        self::assertSame('deferred', $operationLimit['status']);
    }

    public function testMessengerDispatcherCoversLimitedDuplicateAndQueuedOutcomes(): void
    {
        $bus = $this->createMock(\Symfony\Component\Messenger\MessageBusInterface::class);
        $tracker = $this->createMock(\App\Searching\Contract\Indexing\SearchReindexJobTrackerInterface::class);
        $guard = $this->createMock(\App\Searching\Contract\Indexing\SearchReindexDuplicateGuardInterface::class);
        $limiter = $this->createMock(SearchOperationLimiterInterface::class);
        $limiter->method('decide')->willReturnOnConsecutiveCalls(
            SearchOperationLimitDecision::reject('busy', 4),
            SearchOperationLimitDecision::allow(),
            SearchOperationLimitDecision::allow(),
        );
        $duplicate = new \App\Searching\Entity\SearchReindexJobEntity('job-duplicate', 'ordering', 'order');
        $duplicate->markQueued('messenger', 'duplicate-key');
        $guard->method('findOpenDuplicate')->willReturnOnConsecutiveCalls($duplicate, null);
        $created = new \App\Searching\Entity\SearchReindexJobEntity('job-created', 'ordering', 'order');
        $tracker->expects(self::once())->method('request')->willReturn($created);
        $tracker->expects(self::once())->method('markQueued')->with('job-created', 'messenger', self::isType('string'));
        $bus->expects(self::once())->method('dispatch')->willReturnCallback(static fn (object $message): \Symfony\Component\Messenger\Envelope => new \Symfony\Component\Messenger\Envelope($message));

        $dispatcher = new \App\Searching\Service\Indexing\SearchMessengerReindexDispatcher(
            $bus,
            $tracker,
            new \App\Searching\Builder\Indexing\SearchReindexIdempotencyKeyBuilder(),
            $guard,
            $limiter,
            4,
        );

        self::assertSame('limited', $dispatcher->dispatch('ordering', 'order')->jobId);
        self::assertSame('job-duplicate', $dispatcher->dispatch('ordering', 'order')->jobId);
        $queued = $dispatcher->dispatch('ordering', 'order', null, 'user-1');
        self::assertSame('job-created', $queued->jobId);
        self::assertTrue($queued->queued);
        self::assertSame(4, $queued->metadata['max_attempts']);
    }

    public function testReindexIdentityAndMessageCoverNullBlankAndExplicitInputs(): void
    {
        $builder = new \App\Searching\Builder\Indexing\SearchReindexIdempotencyKeyBuilder();
        self::assertSame($builder->build(null, null), $builder->build(' ', ''));
        self::assertSame($builder->build('ORDERING', 'Order'), $builder->build(' ordering ', ' order '));

        $empty = new \App\Searching\Message\SearchReindexMessage('job-empty');
        self::assertNull($empty->getChangedSinceDate());
        $explicit = new \App\Searching\Message\SearchReindexMessage('job-explicit', idempotencyKey: 'known-key');
        self::assertSame('known-key', $explicit->getDeduplicationKey());
    }
}
