<?php

declare(strict_types=1);

namespace App\Searching\Service\Tuning;

use App\Searching\ServiceInterface\Tuning\SearchQueryTuningResolverInterface;
use App\Searching\Value\Query\SearchQuery;
use App\Searching\Value\Tuning\SearchQueryTuning;

final class NullSearchQueryTuningResolver implements SearchQueryTuningResolverInterface
{
    public function resolve(SearchQuery $query): SearchQueryTuning
    {
        return new SearchQueryTuning();
    }
}
