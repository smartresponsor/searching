<?php

declare(strict_types=1);

namespace App\Searching\Contract\Tuning;

use App\Searching\ValueObject\Query\SearchQuery;
use App\Searching\ValueObject\Tuning\SearchQueryTuning;

interface SearchQueryTuningResolverInterface
{
    public function resolve(SearchQuery $query): SearchQueryTuning;
}
