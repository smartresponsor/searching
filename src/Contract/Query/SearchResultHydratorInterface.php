<?php

declare(strict_types=1);

namespace App\Searching\Contract\Query;

use App\Searching\ValueObject\Result\SearchResultHydrationResult;
use App\Searching\ValueObject\Result\SearchResultItem;

interface SearchResultHydratorInterface
{
    /**
     * @param list<SearchResultItem> $items
     */
    public function hydrate(array $items): SearchResultHydrationResult;
}
