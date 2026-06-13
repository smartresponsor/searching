<?php

declare(strict_types=1);

namespace App\Searching\ServiceInterface\Query;

use App\Searching\Value\Query\SearchQuery;
use App\Searching\Value\Result\SearchResult;

interface SearchQueryExecutorInterface
{
    public function execute(SearchQuery $query): SearchResult;
}
