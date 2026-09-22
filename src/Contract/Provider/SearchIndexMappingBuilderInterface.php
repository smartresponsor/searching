<?php

declare(strict_types=1);

namespace App\Searching\Contract\Provider;

use App\Searching\ValueObject\Provider\SearchIndexMapping;
use App\Searching\ValueObject\Provider\SearchProviderConfiguration;

interface SearchIndexMappingBuilderInterface
{
    public function build(string $indexName, string $component, string $resourceType, SearchProviderConfiguration $configuration): SearchIndexMapping;
}
