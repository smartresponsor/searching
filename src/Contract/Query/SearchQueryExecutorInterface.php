<?php

declare(strict_types=1);

namespace App\Searching\Contract\Query;

use App\Searching\ValueObject\Query\SearchQuery;
use App\Searching\ValueObject\Result\SearchResult;

/**
 * Defines the search query executor interface responsibility within the Searching component runtime and its typed boundaries.
 */
interface SearchQueryExecutorInterface
{
    /**
     * Executes the execute operation through the Searching component runtime boundary.
     */
    public function execute(SearchQuery $query): SearchResult;
}
