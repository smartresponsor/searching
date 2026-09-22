<?php

declare(strict_types=1);

namespace App\Searching\Contract\Security;

use App\Searching\ValueObject\Query\SearchQuery;
use App\Searching\ValueObject\Result\SearchResultItem;
use App\Searching\ValueObject\Security\SearchPermissionDecision;

interface SearchPermissionCheckerInterface
{
    public function decide(SearchResultItem $item, SearchQuery $query): SearchPermissionDecision;
}
