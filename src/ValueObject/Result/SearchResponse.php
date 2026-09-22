<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Result;

/**
 * Defines the search response responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchResponse
{
    /**
     * @param list<SearchResponseItem>       $items
     * @param list<SearchFacetResponse>      $facets
     * @param list<SearchSuggestionResponse> $suggestions
     * @param array<string, mixed>           $metadata
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
