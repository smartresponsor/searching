<?php

declare(strict_types=1);

namespace App\Searching\Contract\Provider;

use App\Searching\ValueObject\Provider\SearchBackendQuery;
use App\Searching\ValueObject\Provider\SearchProviderConfiguration;
use App\Searching\ValueObject\Query\SearchSuggestionQuery;

/**
 * Defines the search backend suggestion builder interface responsibility within the Searching component runtime and its typed boundaries.
 */
interface SearchBackendSuggestionBuilderInterface
{
    /**
     * Builds the build used by the Searching component execution and integration boundaries.
     */
    public function build(SearchSuggestionQuery $query, SearchProviderConfiguration $configuration): SearchBackendQuery;
}
