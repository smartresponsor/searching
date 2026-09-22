<?php

declare(strict_types=1);

namespace App\Searching\Contract\Security;

use App\Searching\ValueObject\Query\SearchQuery;
use App\Searching\ValueObject\Result\SearchResultItem;
use App\Searching\ValueObject\Security\SearchPermissionDecision;

/**
 * Defines the search permission checker interface responsibility within the Searching component runtime and its typed boundaries.
 */
interface SearchPermissionCheckerInterface
{
    /**
     * Executes the decide responsibility defined by the Searching component contract.
     */
    public function decide(SearchResultItem $item, SearchQuery $query): SearchPermissionDecision;
}
