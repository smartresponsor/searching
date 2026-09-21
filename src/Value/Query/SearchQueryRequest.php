<?php

declare(strict_types=1);

namespace App\Searching\Value\Query;

use App\Searching\Value\Observability\SearchExecutionContext;

final readonly class SearchQueryRequest
{
    /**
     * @param list<string>          $components
     * @param list<string>          $resourceTypes
     * @param array<string, mixed>  $filters
     * @param array<string, string> $sort
     * @param list<string>          $userPermissions
     * @param array<string, mixed>  $metadata
     */
    public function __construct(
        public string $query,
        public array $components = [],
        public array $resourceTypes = [],
        public array $filters = [],
        public array $sort = [],
        public int $page = 1,
        public int $limit = 20,
        public ?string $locale = null,
        public ?string $vendorId = null,
        public ?string $userId = null,
        public array $userPermissions = [],
        public bool $includeHighlights = true,
        public bool $includeFacets = true,
        public ?SearchExecutionContext $executionContext = null,
        public array $metadata = [],
    ) {
    }

    public function toInternalQuery(): SearchQuery
    {
        return new SearchQuery(
            query: $this->query,
            components: $this->components,
            resourceTypes: $this->resourceTypes,
            filters: $this->filters,
            sort: $this->sort,
            page: $this->page,
            limit: $this->limit,
            locale: $this->locale,
            vendorId: $this->vendorId,
            userId: $this->userId,
            includeHighlights: $this->includeHighlights,
            includeFacets: $this->includeFacets,
            userPermissions: $this->userPermissions,
            executionContext: $this->executionContext,
        );
    }
}
