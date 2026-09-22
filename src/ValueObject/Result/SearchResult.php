<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Result;

/**
 * Defines the search result responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchResult
{
    /**
     * @param list<SearchResultItem> $items
     * @param list<SearchFacet>      $facets
     * @param list<SearchSuggestion> $suggestions
     * @param array<string, mixed>   $metadata
     */
    public function __construct(
        public string $query,
        public int $total,
        public int $page,
        public int $limit,
        public array $items,
        public array $facets = [],
        public array $suggestions = [],
        public array $metadata = [],
    ) {
    }
}
