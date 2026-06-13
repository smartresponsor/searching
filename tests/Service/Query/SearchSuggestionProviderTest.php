<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service\Query;

use App\Searching\Service\Query\SearchSuggestionProvider;
use App\Searching\ServiceInterface\Provider\SearchProviderInterface;
use App\Searching\Value\Document\SearchDocument;
use App\Searching\Value\Provider\SearchProviderResult;
use App\Searching\Value\Provider\SearchProviderStatus;
use App\Searching\Value\Query\SearchQuery;
use App\Searching\Value\Query\SearchSuggestionQuery;
use App\Searching\Value\Result\SearchSuggestion;
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

        self::assertSame('pho', $backend->lastQuery?->query);
        self::assertSame(['cataloging'], $backend->lastQuery?->components);
        self::assertSame(['product'], $backend->lastQuery?->resourceTypes);
        self::assertSame(5, $backend->lastQuery?->limit);
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
