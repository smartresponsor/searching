<?php

declare(strict_types=1);

namespace App\Searching\Contract\Tuning;

use App\Searching\ValueObject\Query\SearchQuery;
use App\Searching\ValueObject\Tuning\SearchQueryTuning;

/**
 * Defines the search query tuning resolver interface responsibility within the Searching component runtime and its typed boundaries.
 */
interface SearchQueryTuningResolverInterface
{
    /**
     * Resolves the resolve required by the Searching component execution flow.
     */
    public function resolve(SearchQuery $query): SearchQueryTuning;
}
