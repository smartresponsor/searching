<?php

declare(strict_types=1);

namespace App\Searching\Resolver\Tuning;

use App\Searching\Contract\Tuning\SearchQueryTuningResolverInterface;
use App\Searching\ValueObject\Query\SearchQuery;
use App\Searching\ValueObject\Tuning\SearchQueryTuning;

final class SearchNullQueryTuningResolver implements SearchQueryTuningResolverInterface
{
    public function resolve(SearchQuery $query): SearchQueryTuning
    {
        return new SearchQueryTuning();
    }
}
