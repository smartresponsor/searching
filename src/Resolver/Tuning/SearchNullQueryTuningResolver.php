<?php

declare(strict_types=1);

namespace App\Searching\Resolver\Tuning;

use App\Searching\Contract\Tuning\SearchQueryTuningResolverInterface;
use App\Searching\ValueObject\Query\SearchQuery;
use App\Searching\ValueObject\Tuning\SearchQueryTuning;

/**
 * Defines the search null query tuning resolver responsibility within the Searching component runtime and its typed boundaries.
 */
final class SearchNullQueryTuningResolver implements SearchQueryTuningResolverInterface
{
    /**
     * Resolves the resolve required by the Searching component execution flow.
     */
    public function resolve(SearchQuery $query): SearchQueryTuning
    {
        return new SearchQueryTuning();
    }
}
