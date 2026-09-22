<?php

declare(strict_types=1);

namespace App\Searching\Resolver\Query;

use App\Faceting\DTO\Aggregation\FacetAggregationResultDTO;
use App\Searching\Value\Result\SearchFacet;

/**
 * Accepts Faceting-owned aggregation semantics at the Searching consumer boundary.
 */
final class SearchFacetAggregationResolver
{
    public function resolve(FacetAggregationResultDTO $aggregation): SearchFacet
    {
        $buckets = [];

        foreach ($aggregation->buckets as $bucket) {
            $buckets[$bucket->valueIdentifier->toString()] = $bucket->count;
        }

        return new SearchFacet(
            $aggregation->facetIdentifier->toString(),
            $buckets,
        );
    }
}
