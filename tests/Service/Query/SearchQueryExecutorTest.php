<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service\Query;

use App\Searching\Service\Flow\NullSearchOperationLimiter;
use App\Searching\Service\Provider\NullSearchProvider;
use App\Searching\Service\Query\NullSearchQueryLogger;
use App\Searching\Service\Query\SearchQueryExecutor;
use App\Searching\Service\Query\SearchResultHydrator;
use App\Searching\Service\Security\SearchPermissionChecker;
use App\Searching\Service\Security\SearchPermissionFilter;
use App\Searching\ServiceInterface\Producer\SearchResultItemHydratorInterface;
use App\Searching\ServiceInterface\Provider\SearchProviderInterface;
use App\Searching\ServiceInterface\Query\SearchQueryLoggerInterface;
use App\Searching\Value\Document\SearchDocument;
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
            new NullSearchProvider(),
            new SearchResultHydrator(),
            new SearchPermissionFilter(new SearchPermissionChecker()),
            new NullSearchQueryLogger(),
            new NullSearchOperationLimiter(),
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
        );

        $result = $executor->execute(new SearchQuery('invoice', userId: 'user-1'));

        self::assertSame(1, $result->total);
        self::assertSame('public', $result->items[0]->resourceId);
        self::assertSame(2, $result->metadata['provider_total']);
        self::assertSame(1, $result->metadata['permission_denied_count']);
        self::assertSame(1, $logger->lastTrace?->deniedCount);
        self::assertSame(1, $logger->lastTrace?->returnedTotal);
        self::assertSame('fake', $result->metadata['query_trace']['provider_name']);
    }

    public function testExecuteDropsStaleItemsThroughHydratorBeforePermissionFiltering(): void
    {
        $hydrator = new SearchResultHydrator();
        $hydrator->add(new FakeOrderingResultHydrator());

        $executor = new SearchQueryExecutor(
            new FakeResultSearchProvider(),
            $hydrator,
            new SearchPermissionFilter(new SearchPermissionChecker()),
            new NullSearchQueryLogger(),
            new NullSearchOperationLimiter(),
        );

        $result = $executor->execute(new SearchQuery('invoice', userId: 'user-1'));

        self::assertSame(1, $result->metadata['hydration_dropped_count']);
        self::assertSame('source_missing_or_stale', $result->metadata['hydration_dropped_items'][0]['reason']);
        self::assertSame(1, $result->metadata['permission_original_count']);
        self::assertSame(1, $result->total);
        self::assertSame('public', $result->items[0]->resourceId);
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
