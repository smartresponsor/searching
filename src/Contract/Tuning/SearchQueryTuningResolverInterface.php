<?php

declare(strict_types=1);

namespace App\Searching\Contract\Tuning;

use App\Searching\Value\Query\SearchQuery;
use App\Searching\Value\Tuning\SearchQueryTuning;

interface SearchQueryTuningResolverInterface
{
    public function resolve(SearchQuery $query): SearchQueryTuning;
}
