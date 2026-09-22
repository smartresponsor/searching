<?php

declare(strict_types=1);

namespace App\Searching\Contract\Query;

use App\Searching\ValueObject\Provider\SearchCapability;
use App\Searching\ValueObject\Query\SearchQueryRequest;
use App\Searching\ValueObject\Result\SearchResponse;

/**
 * Defines the search response provider interface responsibility within the Searching component runtime and its typed boundaries.
 */
interface SearchResponseProviderInterface
{
    /**
     * Searches the search through the Searching component query boundary.
     */
    public function search(SearchQueryRequest $query): SearchResponse;

    public function getCapability(): SearchCapability;
}
