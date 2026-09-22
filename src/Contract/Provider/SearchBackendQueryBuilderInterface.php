<?php

declare(strict_types=1);

namespace App\Searching\Contract\Provider;

use App\Searching\ValueObject\Provider\SearchBackendQuery;
use App\Searching\ValueObject\Provider\SearchProviderConfiguration;
use App\Searching\ValueObject\Query\SearchQuery;

/**
 * Defines the search backend query builder interface responsibility within the Searching component runtime and its typed boundaries.
 */
interface SearchBackendQueryBuilderInterface
{
    /**
     * Builds the build used by the Searching component execution and integration boundaries.
     */
    public function build(SearchQuery $query, SearchProviderConfiguration $configuration): SearchBackendQuery;
}
