<?php

declare(strict_types=1);

namespace App\Searching\Contract\Provider;

use App\Searching\Value\Provider\SearchIndexMapping;
use App\Searching\Value\Provider\SearchProviderConfiguration;

interface SearchIndexMappingBuilderInterface
{
    public function build(string $indexName, string $component, string $resourceType, SearchProviderConfiguration $configuration): SearchIndexMapping;
}
