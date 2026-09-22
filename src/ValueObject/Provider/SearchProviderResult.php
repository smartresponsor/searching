<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Provider;

use App\Searching\ValueObject\Result\SearchFacet;
use App\Searching\ValueObject\Result\SearchResultItem;
use App\Searching\ValueObject\Result\SearchSuggestion;

final readonly class SearchProviderResult
{
    /**
     * @param list<SearchResultItem> $items
     * @param list<SearchFacet>      $facets
     * @param list<SearchSuggestion> $suggestions
     * @param array<string, mixed>   $metadata
     */
    public function __construct(
        public int $total,
        public array $items,
        public array $facets = [],
        public array $suggestions = [],
        public array $metadata = [],
    ) {
    }
}
