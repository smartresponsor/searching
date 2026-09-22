<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service\Query;

use App\Searching\Contract\Provider\SearchProviderInterface;
use App\Searching\Provider\Query\SearchSuggestionProvider;
use App\Searching\ValueObject\Document\SearchDocument;
use App\Searching\ValueObject\Provider\SearchProviderResult;
use App\Searching\ValueObject\Provider\SearchProviderStatus;
use App\Searching\ValueObject\Query\SearchQuery;
use App\Searching\ValueObject\Query\SearchSuggestionQuery;
use App\Searching\ValueObject\Result\SearchSuggestion;
use PHPUnit\Framework\TestCase;

final class SearchSuggestionProviderTest extends TestCase
{
    public function testItDelegatesSuggestionQueryToActiveSearchProvider(): void
    {
        $backend = new CapturingSuggestionSearchProvider();
        $provider = new SearchSuggestionProvider($backend);

        $suggestions = $provider->suggestByQuery(new SearchSuggestionQuery(
            query: 'pho',
            components: ['cataloging'],
            resourceTypes: ['product'],
            limit: 5,
        ));

        $lastQuery = $backend->lastQuery;
        if (null === $lastQuery) {
            self::fail('Expected delegated suggestion query.');
        }

        self::assertSame('pho', $lastQuery->query);
        self::assertSame(['cataloging'], $lastQuery->components);
        self::assertSame(['product'], $lastQuery->resourceTypes);
        self::assertSame(5, $lastQuery->limit);
        self::assertSame('phone', $suggestions[0]->text);
    }

    public function testItReturnsEmptySuggestionsForBlankQuery(): void
    {
        $backend = new CapturingSuggestionSearchProvider();
        $provider = new SearchSuggestionProvider($backend);

        self::assertSame([], $provider->suggest('   ', 10));
        self::assertNull($backend->lastQuery);
    }
}

final class CapturingSuggestionSearchProvider implements SearchProviderInterface
{
    public ?SearchSuggestionQuery $lastQuery = null;

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
        return new SearchProviderResult(0, []);
    }

    public function suggest(SearchSuggestionQuery $query): array
    {
        $this->lastQuery = $query;

        return [new SearchSuggestion('phone', 1.0, 'cataloging', 'product', '42')];
    }

    public function getStatus(): SearchProviderStatus
    {
        return new SearchProviderStatus('capturing', true, 'available');
    }
}
