<?php

declare(strict_types=1);

namespace App\Searching\Tests\Resolver\Query;

use App\Faceting\DTO\Aggregation\FacetAggregationBucketDTO;
use App\Faceting\DTO\Aggregation\FacetAggregationResultDTO;
use App\Faceting\ValueObject\Definition\Facet\FacetCode;
use App\Faceting\ValueObject\Definition\Facet\FacetValueIdentifier;
use App\Searching\Resolver\Query\SearchFacetAggregationResolver;
use PHPUnit\Framework\TestCase;

final class SearchFacetAggregationResolverTest extends TestCase
{
    public function testItAcceptsFacetingAggregationContractWithoutOwningFacetSemantics(): void
    {
        $aggregation = new FacetAggregationResultDTO(
            new FacetCode('brand'),
            [
                new FacetAggregationBucketDTO(new FacetValueIdentifier('brand:nike'), 8, 20),
                new FacetAggregationBucketDTO(new FacetValueIdentifier('brand:adidas'), 12, 10),
            ],
            matchedResourceCount: 20,
        );

        $facet = (new SearchFacetAggregationResolver())->resolve($aggregation);

        self::assertSame('brand', $facet->identifier);
        self::assertSame([
            'brand:adidas' => 12,
            'brand:nike' => 8,
        ], $facet->buckets);
    }
}
