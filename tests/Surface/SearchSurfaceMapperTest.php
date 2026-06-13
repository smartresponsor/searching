<?php

declare(strict_types=1);

namespace App\Searching\Tests\Surface;

use App\Searching\Service\SearchSurfaceMapper;
use App\Searching\Value\Result\SearchFacet;
use App\Searching\Value\Result\SearchHighlight;
use App\Searching\Value\Result\SearchResult;
use App\Searching\Value\Result\SearchResultItem;
use PHPUnit\Framework\TestCase;

final class SearchSurfaceMapperTest extends TestCase
{
    public function testItMapsInternalResultToSurfaceResultAndStripsRawBackendMetadata(): void
    {
        $mapper = new SearchSurfaceMapper();
        $result = $mapper->mapResult(new SearchResult(
            query: 'phone',
            total: 1,
            page: 1,
            limit: 20,
            items: [new SearchResultItem(
                component: 'cataloging',
                resourceType: 'product',
                resourceId: '42',
                title: 'Phone',
                summary: 'Smartphone',
                routeName: 'catalog_product_show',
                routeParameters: ['id' => 42],
                score: 9.5,
                highlights: [new SearchHighlight('title', ['<em>Phone</em>'])],
                metadata: ['raw_hit' => ['secret' => true], 'visibility' => 'public'],
            )],
            facets: [new SearchFacet('brand', ['Acme' => 2])],
            metadata: ['raw_response' => ['hidden' => true], 'provider' => 'null'],
        ));

        self::assertSame('phone', $result->query);
        self::assertSame(1, $result->total);
        self::assertSame('Phone', $result->items[0]->title);
        self::assertArrayNotHasKey('raw_hit', $result->items[0]->metadata);
        self::assertArrayNotHasKey('raw_response', $result->metadata);
        self::assertSame('public', $result->items[0]->metadata['visibility']);
        self::assertSame('brand', $result->facets[0]->nameEntity);
    }
}
