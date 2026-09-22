<?php

declare(strict_types=1);

namespace App\Searching\Contract\Query;

use App\Searching\ValueObject\Query\SearchQuery;
use App\Searching\ValueObject\Result\SearchResult;

interface SearchQueryExecutorInterface
{
    public function execute(SearchQuery $query): SearchResult;
}
