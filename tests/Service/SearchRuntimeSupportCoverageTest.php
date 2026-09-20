<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service;

use App\Searching\Builder\Provider\SearchIndexNameBuilder;
use App\Searching\Contract\Indexing\SearchReindexCoordinatorInterface;
use App\Searching\Contract\Indexing\SearchReindexJobTrackerInterface;
use App\Searching\Contract\Provider\SearchBackendClientInterface;
use App\Searching\Contract\Provider\SearchBackendQueryBuilderInterface;
use App\Searching\Contract\Provider\SearchBackendSuggestionBuilderInterface;
use App\Searching\Contract\Provider\SearchBulkOperationBuilderInterface;
use App\Searching\Contract\Provider\SearchIndexMappingBuilderInterface;
use App\Searching\Event\SearchDocumentIndexedEvent;
use App\Searching\Event\SearchQueryExecutedEvent;
use App\Searching\Event\SearchReindexRequestedEvent;
use App\Searching\Handler\SearchReindexMessageHandler;
use App\Searching\Message\SearchReindexMessage;
use App\Searching\Provider\Backend\SearchElasticsearchProvider;
use App\Searching\Resolver\Tuning\SearchNullQueryTuningResolver;
use App\Searching\Service\Indexing\SearchNullIndexLifecycleRegistrySynchronizer;
use App\Searching\Service\Indexing\SearchNullReindexDuplicateGuard;
use App\Searching\Service\Provider\SearchQueryPayloadMapper;
use App\Searching\Value\Document\SearchDocument;
use App\Searching\Value\Document\SearchDocumentField;
use App\Searching\Value\Document\SearchDocumentIdentity;
use App\Searching\Value\Provider\SearchBackendQuery;
use App\Searching\Value\Provider\SearchBulkOperation;
use App\Searching\Value\Provider\SearchBulkOperationSet;
use App\Searching\Value\Provider\SearchIndexLifecycleResult;
use App\Searching\Value\Provider\SearchIndexMapping;
use App\Searching\Value\Provider\SearchProviderConfiguration;
use App\Searching\Value\Provider\SearchProviderResult;
use App\Searching\Value\Provider\SearchProviderStatus;
use App\Searching\Value\Query\SearchFilter;
use App\Searching\Value\Query\SearchPagination;
use App\Searching\Value\Query\SearchQuery;
use App\Searching\Value\Query\SearchSort;
use App\Searching\Value\Query\SearchSuggestionQuery;
use App\Searching\Value\Result\SearchSuggestion;
use PHPUnit\Framework\TestCase;

final class SearchRuntimeSupportCoverageTest extends TestCase
{
    public function testBackendProviderExecutesExistingProviderContract(): void
    {
        $queryBuilder = $this->createMock(SearchBackendQueryBuilderInterface::class);
        $queryBuilder->method('build')->willReturn(new SearchBackendQuery(['match_all' => new \stdClass()], [], [], 0, 10));
        $suggestionBuilder = $this->createMock(SearchBackendSuggestionBuilderInterface::class);
        $suggestionBuilder->method('build')->willReturn(new SearchBackendQuery(['suggest' => 'need'], [], [], 0, 5));
        $bulkBuilder = $this->createMock(SearchBulkOperationBuilderInterface::class);
        $bulkBuilder->method('buildIndexOperation')->willReturn(new SearchBulkOperation('index', 'sr_ordering_order', 'doc-1', ['title' => 'Needle']));
        $bulkBuilder->method('buildIndexOperations')->willReturn(new SearchBulkOperationSet([
            new SearchBulkOperation('index', 'sr_ordering_order', 'doc-1', ['title' => 'Needle']),
        ]));
        $mappingBuilder = $this->createMock(SearchIndexMappingBuilderInterface::class);
        $mappingBuilder->method('build')->willReturn(new SearchIndexMapping('sr_ordering_order', [], ['title' => ['type' => 'text']], ['version' => 1]));
        $client = $this->createMock(SearchBackendClientInterface::class);
        $client->method('search')->willReturnOnConsecutiveCalls(
            new SearchProviderResult(0, []),
            new SearchProviderResult(0, [], suggestions: [new SearchSuggestion('Needle', 1.0)]),
        );
        $client->method('getStatus')->willReturn(new SearchProviderStatus('elasticsearch', true, 'available'));
        $client->method('indexExists')->willReturnOnConsecutiveCalls(true, false, true);
        $client->expects(self::once())
            ->method('delete')
            ->with('sr_ordering_order', 'ordering_order_42');

        $provider = new SearchElasticsearchProvider(
            new SearchProviderConfiguration('elasticsearch', true, 'http://localhost:9200', 'sr'),
            new SearchIndexNameBuilder(),
            $queryBuilder,
            $suggestionBuilder,
            $bulkBuilder,
            $mappingBuilder,
            $client,
        );
        $document = new SearchDocument('ordering', 'order', '42', 'Needle', null, null, [], [], [], null, null, null, 'order_show', [], new \DateTimeImmutable('@0'));

        $provider->index($document);
        $provider->bulkIndex([$document]);
        $provider->delete('ordering', 'order', '42');
        self::assertSame(0, $provider->search(new SearchQuery('needle'))->total);
        self::assertSame('Needle', $provider->suggest(new SearchSuggestionQuery('need'))[0]->text);
        self::assertTrue($provider->getStatus()->available);
        self::assertTrue($provider->indexExists('ordering', 'order'));
        self::assertSame('created', $provider->ensureIndex('ordering', 'order')->status);
        self::assertSame('deleted', $provider->deleteIndex('ordering', 'order')->status);
    }

    public function testSmallRuntimeContractsExposeDeterministicValues(): void
    {
        $query = new SearchQuery('needle', components: ['ordering'], page: 2, limit: 5);
        $payload = (new SearchQueryPayloadMapper())->map($query);
        self::assertSame(5, $payload['offset']);
        self::assertSame([], (new SearchNullQueryTuningResolver())->resolve($query)->matchedSynonyms);

        $identity = new SearchDocumentIdentity('ordering', 'order', '42');
        self::assertSame('ordering:order:42', $identity->toKey());
        self::assertSame('title', (new SearchDocumentField('title', 'Needle'))->nameEntity);
        self::assertSame('status', (new SearchFilter('status', 'open'))->field);
        self::assertSame(2, (new SearchPagination(2, 50))->page);
        self::assertSame('desc', (new SearchSort('createdAt', 'desc'))->direction);
        self::assertSame('indexed', (new SearchDocumentIndexedEvent('indexed'))->id);
        self::assertSame('query', (new SearchQueryExecutedEvent('query'))->id);
        self::assertSame('reindex', (new SearchReindexRequestedEvent('reindex'))->id);

        $nullSync = new SearchNullIndexLifecycleRegistrySynchronizer();
        self::assertFalse($nullSync->sync('ordering', 'order', new SearchIndexLifecycleResult('null', 'idx', 'ensure', 'ready', false))->synced);
        self::assertNull((new SearchNullReindexDuplicateGuard())->findOpenDuplicate('key'));
    }

    public function testValueContractsCoverCollectionMetadataAndContextVariants(): void
    {
        $operation = new SearchBulkOperation('index', 'idx-a', 'doc-1', ['title' => 'Needle']);
        self::assertSame('idx-a', $operation->toArray()['indexName']);

        $set = new SearchBulkOperationSet([
            $operation,
            new SearchBulkOperation('delete', 'idx-b', 'doc-2'),
            new SearchBulkOperation('index', 'idx-a', 'doc-3'),
        ]);
        self::assertCount(3, $set);
        self::assertCount(3, iterator_to_array($set));
        self::assertCount(2, $set->groupedByIndex()['idx-a']);

        $emptyTuning = new \App\Searching\Value\Tuning\SearchQueryTuning();
        self::assertFalse($emptyTuning->hasExpandedTerms());
        self::assertFalse($emptyTuning->hasFieldWeights());
        $tuning = new \App\Searching\Value\Tuning\SearchQueryTuning(['phone'], ['title' => 2], ['mobile' => ['phone']], ['default']);
        self::assertTrue($tuning->hasExpandedTerms());
        self::assertTrue($tuning->hasFieldWeights());
        self::assertSame(['phone'], $tuning->toMetadata()['expanded_terms']);

        $context = \App\Searching\Value\Observability\SearchExecutionContext::create(' corr ', ' req ', ' bridging ', ' search ', ' user ', ['key' => 'value']);
        self::assertSame('corr', $context->correlationId);
        self::assertSame('search.next', $context->withSourceOperation('search.next')->sourceOperation);
        $contextMetadata = $context->toMetadata()['metadata'];
        self::assertIsArray($contextMetadata);
        self::assertSame('value', $contextMetadata['key']);
        self::assertStringStartsWith('srch_', \App\Searching\Value\Observability\SearchExecutionContext::create(' ')->correlationId);

        $limitedQuery = \App\Searching\Value\Flow\SearchOperationLimitRequest::forSearchQuery(new SearchQuery(
            'needle',
            userId: 'user-1',
            tenantId: 'tenant-1',
            limit: 100,
        ));
        self::assertSame(10, $limitedQuery->cost);
        self::assertSame('user-1:tenant-1:needle', $limitedQuery->identity);
        $anonymousQuery = \App\Searching\Value\Flow\SearchOperationLimitRequest::forSearchQuery(new SearchQuery('', limit: 0));
        self::assertSame(1, $anonymousQuery->cost);
        self::assertSame('anonymous', $anonymousQuery->identity);

        try {
            new \App\Searching\Value\Flow\SearchOperationLimitRequest('invalid', cost: 0);
            self::fail('Expected invalid operation cost to be rejected.');
        } catch (\InvalidArgumentException $exception) {
            self::assertStringContainsString('greater than zero', $exception->getMessage());
        }

        try {
            new SearchBulkOperation('replace', 'idx-a', 'doc-invalid');
            self::fail('Expected invalid bulk operation to be rejected.');
        } catch (\InvalidArgumentException $exception) {
            self::assertStringContainsString('index', $exception->getMessage());
        }
    }

    public function testReindexHandlerDelegatesAndMarksDispatchFailure(): void
    {
        $coordinator = $this->createMock(SearchReindexCoordinatorInterface::class);
        $tracker = $this->createStub(SearchReindexJobTrackerInterface::class);
        $handler = new SearchReindexMessageHandler($coordinator, $tracker);
        $message = new SearchReindexMessage('job-1', 'ordering', 'order', '2026-09-15T12:00:00+00:00');

        $coordinator->expects(self::once())->method('reindexExistingJob')->with(
            'job-1',
            'ordering',
            'order',
            self::isInstanceOf(\DateTimeImmutable::class),
            null,
        )->willReturn(new \App\Searching\Value\Indexing\SearchReindexResult('job-1', 1, 1));
        $handler($message);
        self::assertSame('2026-09-15', $message->getChangedSinceDate()?->format('Y-m-d'));
        self::assertNotSame('', $message->getDeduplicationKey());

        $failure = new \RuntimeException('dispatch failed');
        $failedCoordinator = $this->createStub(SearchReindexCoordinatorInterface::class);
        $failedCoordinator->method('reindexExistingJob')->willThrowException($failure);
        $failedTracker = $this->createMock(SearchReindexJobTrackerInterface::class);
        $failedTracker->expects(self::once())->method('markDispatchFailed')->with('job-1', 'dispatch failed');

        try {
            (new SearchReindexMessageHandler($failedCoordinator, $failedTracker))($message);
            self::fail('Expected runtime exception was not rethrown.');
        } catch (\RuntimeException $exception) {
            self::assertSame($failure, $exception);
        }
    }
}
