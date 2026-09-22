<?php

declare(strict_types=1);

namespace App\Searching\Contract\Provider;

use App\Searching\ValueObject\Provider\SearchIndexMapping;
use App\Searching\ValueObject\Provider\SearchProviderConfiguration;

/**
 * Defines the search index mapping builder interface responsibility within the Searching component runtime and its typed boundaries.
 */
interface SearchIndexMappingBuilderInterface
{
    /**
     * Builds the build used by the Searching component execution and integration boundaries.
     */
    public function build(string $indexName, string $component, string $resourceType, SearchProviderConfiguration $configuration): SearchIndexMapping;
}
