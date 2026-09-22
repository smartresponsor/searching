<?php

declare(strict_types=1);

namespace App\Searching\Contract\Security;

use App\Searching\ValueObject\Query\SearchQuery;
use App\Searching\ValueObject\Result\SearchResultItem;
use App\Searching\ValueObject\Security\SearchPermissionFilterResult;

interface SearchPermissionFilterInterface
{
    /**
     * @param list<SearchResultItem> $items
     */
    public function filterResult(array $items, SearchQuery $query): SearchPermissionFilterResult;
}
