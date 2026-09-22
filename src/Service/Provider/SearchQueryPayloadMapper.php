<?php

declare(strict_types=1);

namespace App\Searching\Service\Provider;

use App\Searching\ValueObject\Query\SearchQuery;

final class SearchQueryPayloadMapper
{
    /**
     * @return array<string, mixed>
     */
    public function map(SearchQuery $query): array
    {
        return [
            'query' => $query->query,
            'components' => $query->components,
            'resource_types' => $query->resourceTypes,
            'filters' => $query->filters,
            'sort' => $query->sort,
            'page' => $query->page,
            'limit' => $query->limit,
            'offset' => max(0, ($query->page - 1) * $query->limit),
            'locale' => $query->locale,
            'vendor_id' => $query->vendorId,
            'user_id' => $query->userId,
            'user_permissions' => $query->userPermissions,
            'include_highlights' => $query->includeHighlights,
            'include_facets' => $query->includeFacets,
        ];
    }
}
