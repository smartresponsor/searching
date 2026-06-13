<?php

declare(strict_types=1);

namespace App\Searching\Value\Query;

use App\Searching\Value\Observability\SearchExecutionContext;

final readonly class SearchQuery
{
    /**
     * @param list<string>          $components
     * @param list<string>          $resourceTypes
     * @param array<string, mixed>  $filters
     * @param array<string, string> $sort
     * @param list<string>          $userPermissions
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
        public ?string $tenantId = null,
        public ?string $userId = null,
        public bool $includeHighlights = true,
        public bool $includeFacets = true,
        public array $userPermissions = [],
        public ?SearchExecutionContext $executionContext = null,
    ) {
    }
}
