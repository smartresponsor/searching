<?php

declare(strict_types=1);

namespace App\Searching\Contract\Security;

use App\Searching\Value\Query\SearchQuery;
use App\Searching\Value\Result\SearchResultItem;
use App\Searching\Value\Security\SearchPermissionDecision;

interface SearchPermissionCheckerInterface
{
    public function decide(SearchResultItem $item, SearchQuery $query): SearchPermissionDecision;
}
