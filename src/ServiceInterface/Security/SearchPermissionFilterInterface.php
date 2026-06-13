<?php

declare(strict_types=1);

namespace App\Searching\ServiceInterface\Security;

use App\Searching\Value\Query\SearchQuery;
use App\Searching\Value\Result\SearchResultItem;
use App\Searching\Value\Security\SearchPermissionFilterResult;

interface SearchPermissionFilterInterface
{
    /**
     * @param list<SearchResultItem> $items
     */
    public function filterResult(array $items, SearchQuery $query): SearchPermissionFilterResult;
}
