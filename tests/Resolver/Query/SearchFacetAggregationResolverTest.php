<?php

declare(strict_types=1);

namespace App\Searching\Tests\Resolver\Query;

use App\Searching\Resolver\Query\SearchFacetAggregationResolver;
use PHPUnit\Framework\TestCase;

final class SearchFacetAggregationResolverTest extends TestCase
{
    public function testItAcceptsProviderNeutralAggregationData(): void
    {
        $facet = (new SearchFacetAggregationResolver())->resolve('brand', [
            'brand:nike' => 8,
            'brand:adidas' => 12,
        ]);

        self::assertSame('brand', $facet->identifier);
        self::assertSame([
            'brand:adidas' => 12,
            'brand:nike' => 8,
        ], $facet->buckets);
    }
}
