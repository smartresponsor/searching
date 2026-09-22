<?php

declare(strict_types=1);

namespace App\Searching\Contract\Provider;

use App\Searching\ValueObject\Provider\SearchBackendQuery;
use App\Searching\ValueObject\Provider\SearchProviderConfiguration;
use App\Searching\ValueObject\Query\SearchQuery;

interface SearchBackendQueryBuilderInterface
{
    public function build(SearchQuery $query, SearchProviderConfiguration $configuration): SearchBackendQuery;
}
