<?php

declare(strict_types=1);

namespace App\Searching\Contract\Security;

use App\Searching\ValueObject\Query\SearchQuery;
use App\Searching\ValueObject\Result\SearchResultItem;
use App\Searching\ValueObject\Security\SearchPermissionFilterResult;

/**
 * Defines the search permission filter interface responsibility within the Searching component runtime and its typed boundaries.
 */
interface SearchPermissionFilterInterface
{
    /**
     * @param list<SearchResultItem> $items
     */
    public function filterResult(array $items, SearchQuery $query): SearchPermissionFilterResult;
}
