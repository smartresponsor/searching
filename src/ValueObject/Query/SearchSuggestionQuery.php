<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Query;

use App\Searching\ValueObject\Observability\SearchExecutionContext;

final readonly class SearchSuggestionQuery
{
    /**
     * @param list<string>         $components
     * @param list<string>         $resourceTypes
     * @param array<string, mixed> $filters
     */
    public function __construct(
        public string $query,
        public array $components = [],
        public array $resourceTypes = [],
        public array $filters = [],
        public int $limit = 10,
        public ?string $locale = null,
        public ?string $vendorId = null,
        public ?string $userId = null,
        public bool $includeSynonyms = true,
        public bool $includeFuzzy = true,
        public ?SearchExecutionContext $executionContext = null,
    ) {
    }

    public function toSearchQuery(): SearchQuery
    {
        return new SearchQuery(
            query: $this->query,
            components: $this->components,
            resourceTypes: $this->resourceTypes,
            filters: $this->filters,
            page: 1,
            limit: $this->limit,
            locale: $this->locale,
            vendorId: $this->vendorId,
            userId: $this->userId,
            includeHighlights: false,
            includeFacets: false,
            executionContext: $this->executionContext,
        );
    }
}
