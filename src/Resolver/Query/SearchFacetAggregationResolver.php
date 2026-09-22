<?php

declare(strict_types=1);

namespace App\Searching\Resolver\Query;

use App\Searching\ValueObject\Result\SearchFacet;

/**
 * Accepts provider-neutral facet aggregation data at the Searching boundary.
 */
final class SearchFacetAggregationResolver
{
    /**
     * @param array<string, int> $buckets
     */
    public function resolve(
        string $facetIdentifier,
        array $buckets,
    ): SearchFacet {
        return new SearchFacet(
            $facetIdentifier,
            $buckets,
        );
    }
}
