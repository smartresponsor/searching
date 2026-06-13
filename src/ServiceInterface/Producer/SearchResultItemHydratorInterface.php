<?php

declare(strict_types=1);

namespace App\Searching\ServiceInterface\Producer;

use App\Searching\Value\Result\SearchResultItem;

/**
 * Producer-side contract for validating and refreshing result items returned by a search backend.
 */
interface SearchResultItemHydratorInterface
{
    public function getSearchableComponentName(): string;

    public function getSearchableResourceName(): string;

    /**
     * Return a refreshed result item, or null when the source record is deleted, disabled, stale, or no longer visible.
     */
    public function hydrateSearchResultItem(SearchResultItem $item): ?SearchResultItem;
}
