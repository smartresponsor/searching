<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service\Query;

use App\Searching\Contract\Flow\SearchOperationLimiterInterface;
use App\Searching\Contract\Producer\SearchResultItemHydratorInterface;
use App\Searching\Contract\Provider\SearchProviderInterface;
use App\Searching\Contract\Query\SearchQueryLoggerInterface;
use App\Searching\Contract\Query\SearchResultHydratorInterface;
use App\Searching\Contract\Security\SearchPermissionFilterInterface;
use App\Searching\Exception\SearchOperationLimitedException;
use App\Searching\Provider\Backend\SearchNullProvider;
use App\Searching\Service\Flow\SearchNullOperationLimiter;
use App\Searching\Service\Query\SearchNullQueryLogger;
use App\Searching\Service\Query\SearchQueryExecutor;
use App\Searching\Service\Query\SearchResultHydrator;
use App\Searching\Service\Security\SearchPermissionChecker;
use App\Searching\Service\Security\SearchPermissionFilter;
use App\Searching\Value\Document\SearchDocument;
use App\Searching\Value\Flow\SearchOperationLimitDecision;
use App\Searching\Value\Observability\SearchExecutionContext;
use App\Searching\Value\Provider\SearchProviderResult;
use App\Searching\Value\Provider\SearchProviderStatus;
use App\Searching\Value\Query\SearchQuery;
use App\Searching\Value\Query\SearchQueryExecutionTrace;
use App\Searching\Value\Query\SearchSuggestionQuery;
use App\Searching\Value\Result\SearchResultItem;
use PHPUnit\Framework\TestCase;

final class SearchQueryExecutorTest extends TestCase
{
    public function testExecuteReturnsCanonicalSearchResult(): void
    {
        $executor = new SearchQueryExecutor(
            new SearchNullProvider(),
            new SearchResultHydrator(),
            new SearchPermissionFilter(new SearchPermissionChecker()),
            new SearchNullQueryLogger(),
            new SearchNullOperationLimiter(),
        );
        $result = $executor->execute(new SearchQuery('invoice'));

        self::assertSame('invoice', $result->query);
        self::assertSame(0, $result->total);
        self::assertSame(1, $result->page);
        self::assertTrue($result->metadata['permission_filtering']);
    }

    public function testExecuteAppliesFinalPermissionFilter(): void
    {
        $logger = new CapturingSearchQueryLogger();
        $executor = new SearchQueryExecutor(
            new FakeResultSearchProvider(),
            new SearchResultHydrator(),
            new SearchPermissionFilter(new SearchPermissionChecker()),
            $logger,
            new SearchNullOperationLimiter(),
        );

        $result = $executor->execute(new SearchQuery('invoice', userId: 'user-1'));

        self::assertSame(1, $result->total);
        self::assertSame('public', $result->items[0]->resourceId);
        self::assertSame(2, $result->metadata['provider_total']);
        self::assertSame(1, $result->metadata['permission_denied_count']);
        $trace = $logger->lastTrace;
        if (null === $trace) {
            self::fail('Expected query execution trace.');
        }
        self::assertSame(1, $trace->deniedCount);
        self::assertSame(1, $trace->returnedTotal);
        $queryTrace = $result->metadata['query_trace'] ?? null;
        if (!is_array($queryTrace)) {
            self::fail('Expected query trace metadata.');
        }
        /** @var array{provider_name: string} $queryTrace */
        self::assertSame('fake', $queryTrace['provider_name']);
    }

    public function testExecuteDropsStaleItemsThroughHydratorBeforePermissionFiltering(): void
    {
        $hydrator = new SearchResultHydrator();
        $hydrator->add(new FakeOrderingResultHydrator());

        $executor = new SearchQueryExecutor(
            new FakeResultSearchProvider(),
            $hydrator,
            new SearchPermissionFilter(new SearchPermissionChecker()),
            new SearchNullQueryLogger(),
            new SearchNullOperationLimiter(),
        );

        $result = $executor->execute(new SearchQuery('invoice', userId: 'user-1'));

        self::assertSame(1, $result->metadata['hydration_dropped_count']);
        $droppedItems = $result->metadata['hydration_dropped_items'] ?? null;
        if (!is_array($droppedItems)) {
            self::fail('Expected hydration dropped items metadata.');
        }
        /** @var list<array{reason: string}> $droppedItems */
        self::assertSame('source_missing_or_stale', $droppedItems[0]['reason']);
        self::assertSame(1, $result->metadata['permission_original_count']);
        self::assertSame(1, $result->total);
        self::assertSame('public', $result->items[0]->resourceId);
    }

    public function testExecuteRejectsLimitedQueryAndLogsFailureTrace(): void
    {
        $logger = new CapturingSearchQueryLogger();
        $limiter = $this->createMock(SearchOperationLimiterInterface::class);
        $limiter->method('decide')->willReturn(SearchOperationLimitDecision::reject('quota exceeded', 30));
        $provider = $this->createMock(SearchProviderInterface::class);
        $provider->method('getStatus')->willReturn(new SearchProviderStatus('fake', true, 'available'));

        $executor = new SearchQueryExecutor(
            $provider,
            $this->createMock(SearchResultHydratorInterface::class),
            $this->createMock(SearchPermissionFilterInterface::class),
            $logger,
            $limiter,
        );

        try {
            $executor->execute(new SearchQuery('invoice', userId: 'user-1', vendorId: 'vendor-1'));
            self::fail('Expected query limit exception.');
        } catch (SearchOperationLimitedException $exception) {
            self::assertSame('quota exceeded', $exception->getMessage());
            self::assertSame('search.query', $exception->request->operation);
            self::assertSame('rejected', $exception->decision->status());
        }

        self::assertNotNull($logger->lastTrace);
        self::assertFalse($logger->lastTrace->successful);
        self::assertSame(SearchOperationLimitedException::class, $logger->lastTrace->errorClass);
        self::assertSame('quota exceeded', $logger->lastTrace->errorMessage);
    }

    public function testExecuteCanSkipHydrationAndPermissionFiltering(): void
    {
        $logger = new CapturingSearchQueryLogger();
        $hydrator = $this->createMock(SearchResultHydratorInterface::class);
        $hydrator->expects(self::never())->method('hydrate');
        $filter = $this->createMock(SearchPermissionFilterInterface::class);
        $filter->expects(self::never())->method('filterResult');

        $executor = new SearchQueryExecutor(
            new FakeResultSearchProvider(),
            $hydrator,
            $filter,
            $logger,
            new SearchNullOperationLimiter(),
            resultHydration: false,
            permissionFiltering: false,
        );

        $result = $executor->execute(new SearchQuery(
            'invoice',
            executionContext: new SearchExecutionContext('corr-42', 'req-42', 'ordering', 'lookup'),
        ));

        self::assertSame(2, $result->total);
        self::assertFalse($result->metadata['result_hydration']);
        self::assertFalse($result->metadata['permission_filtering']);
        $queryTrace = $result->metadata['query_trace'] ?? null;
        if (!is_array($queryTrace)) {
            self::fail('Expected query trace metadata.');
        }
        /** @var array{correlation_id: ?string, request_id: ?string} $queryTrace */
        self::assertSame('corr-42', $queryTrace['correlation_id']);
        self::assertSame('req-42', $queryTrace['request_id']);
        self::assertNotNull($logger->lastTrace);
        self::assertSame(0, $logger->lastTrace->deniedCount);
    }

    public function testExecuteLogsAndRethrowsProviderFailure(): void
    {
        $logger = new CapturingSearchQueryLogger();
        $provider = $this->createMock(SearchProviderInterface::class);
        $provider->method('getStatus')->willReturn(new SearchProviderStatus('broken', false, 'offline'));
        $provider->method('search')->willThrowException(new \RuntimeException('backend unavailable'));

        $executor = new SearchQueryExecutor(
            $provider,
            $this->createMock(SearchResultHydratorInterface::class),
            $this->createMock(SearchPermissionFilterInterface::class),
            $logger,
            new SearchNullOperationLimiter(),
        );

        try {
            $executor->execute(new SearchQuery('invoice'));
            self::fail('Expected backend failure.');
        } catch (\RuntimeException $exception) {
            self::assertSame('backend unavailable', $exception->getMessage());
        }

        self::assertNotNull($logger->lastTrace);
        self::assertFalse($logger->lastTrace->successful);
        self::assertSame(\RuntimeException::class, $logger->lastTrace->errorClass);
        self::assertSame('backend unavailable', $logger->lastTrace->errorMessage);
    }
}

final class FakeResultSearchProvider implements SearchProviderInterface
{
    public function index(SearchDocument $document): void
    {
    }

    public function bulkIndex(iterable $documents): void
    {
        foreach ($documents as $_document) {
        }
    }

    public function delete(string $component, string $resourceType, string $resourceId): void
    {
    }

    public function search(SearchQuery $query): SearchProviderResult
    {
        return new SearchProviderResult(
            total: 2,
            items: [
                new SearchResultItem(
                    component: 'ordering',
                    resourceType: 'order',
                    resourceId: 'public',
                    title: 'Public order',
                    summary: null,
                    routeName: 'order_show',
                    routeParameters: ['id' => 'public'],
                    metadata: ['visibility' => 'public'],
                ),
                new SearchResultItem(
                    component: 'ordering',
                    resourceType: 'order',
                    resourceId: 'private',
                    title: 'Private order',
                    summary: null,
                    routeName: 'order_show',
                    routeParameters: ['id' => 'private'],
                    metadata: ['visibility' => 'private', 'ownerId' => 'user-2'],
                ),
            ],
        );
    }

    public function suggest(SearchSuggestionQuery $query): array
    {
        return [];
    }

    public function getStatus(): SearchProviderStatus
    {
        return new SearchProviderStatus('fake', true, 'available');
    }
}

final class FakeOrderingResultHydrator implements SearchResultItemHydratorInterface
{
    public function getSearchableComponentName(): string
    {
        return 'ordering';
    }

    public function getSearchableResourceName(): string
    {
        return 'order';
    }

    public function hydrateSearchResultItem(SearchResultItem $item): ?SearchResultItem
    {
        if ('private' === $item->resourceId) {
            return null;
        }

        return $item;
    }
}

final class CapturingSearchQueryLogger implements SearchQueryLoggerInterface
{
    public ?SearchQueryExecutionTrace $lastTrace = null;

    public function log(SearchQueryExecutionTrace $trace): void
    {
        $this->lastTrace = $trace;
    }
}
