<?php

declare(strict_types=1);

namespace App\Searching\Resolver\Query;

/**
 * Defines the search facet resolver responsibility within the Searching component runtime and its typed boundaries.
 */
final class SearchFacetResolver
{
    /**
     * @param array<string, mixed> $facets
     *
     * @return array<string, mixed>
     */
    public function resolve(array $facets): array
    {
        return $facets;
    }
}
