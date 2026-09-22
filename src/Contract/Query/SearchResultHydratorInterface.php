<?php

declare(strict_types=1);

namespace App\Searching\Contract\Query;

use App\Searching\ValueObject\Result\SearchResultHydrationResult;
use App\Searching\ValueObject\Result\SearchResultItem;

/**
 * Defines the search result hydrator interface responsibility within the Searching component runtime and its typed boundaries.
 */
interface SearchResultHydratorInterface
{
    /**
     * @param list<SearchResultItem> $items
     */
    public function hydrate(array $items): SearchResultHydrationResult;
}
