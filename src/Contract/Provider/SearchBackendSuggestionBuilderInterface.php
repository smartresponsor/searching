<?php

declare(strict_types=1);

namespace App\Searching\Contract\Provider;

use App\Searching\Value\Provider\SearchBackendQuery;
use App\Searching\Value\Provider\SearchProviderConfiguration;
use App\Searching\Value\Query\SearchSuggestionQuery;

interface SearchBackendSuggestionBuilderInterface
{
    public function build(SearchSuggestionQuery $query, SearchProviderConfiguration $configuration): SearchBackendQuery;
}
