<?php

declare(strict_types=1);

namespace App\Searching\Value\Surface;

final readonly class SearchSurfaceResult
{
    /**
     * @param list<SearchSurfaceResultItem> $items
     * @param list<SearchSurfaceFacet>      $facets
     * @param list<SearchSurfaceSuggestion> $suggestions
     * @param array<string, mixed>          $metadata
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
