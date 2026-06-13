<?php

declare(strict_types=1);

namespace App\Searching\ServiceInterface\Provider;

use App\Searching\Value\Provider\SearchBackendQuery;
use App\Searching\Value\Provider\SearchProviderConfiguration;
use App\Searching\Value\Query\SearchQuery;

interface SearchBackendQueryBuilderInterface
{
    public function build(SearchQuery $query, SearchProviderConfiguration $configuration): SearchBackendQuery;
}
