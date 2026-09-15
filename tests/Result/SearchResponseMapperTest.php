<?php

declare(strict_types=1);

namespace App\Searching\Tests\Result;

use App\Searching\Service\SearchResponseMapper;
use App\Searching\Value\Result\SearchFacet;
use App\Searching\Value\Result\SearchHighlight;
use App\Searching\Value\Result\SearchResult;
use App\Searching\Value\Result\SearchResultItem;
use App\Searching\Value\Result\SearchSuggestion;
use PHPUnit\Framework\TestCase;

final class SearchResponseMapperTest extends TestCase
{
    public function testItMapsInternalResultToSurfaceResultAndStripsRawBackendMetadata(): void
    {
        $mapper = new SearchResponseMapper();
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

    public function testItMapsSuggestionRouteMetadataAndDropsRawProviderPayload(): void
    {
        $mapped = (new SearchResponseMapper())->mapSuggestion(new SearchSuggestion(
            text: 'invoice',
            score: 0.8,
            component: 'ordering',
            resourceType: 'order',
            resourceId: '42',
            metadata: [
                'routeName' => 'order_show',
                'routeParameters' => ['id' => 42],
                'provider_payload' => ['secret' => true],
                'safe' => 'visible',
            ],
        ));

        self::assertSame('order_show', $mapped->routeName);
        self::assertSame(['id' => 42], $mapped->routeParameters);
        self::assertArrayNotHasKey('provider_payload', $mapped->metadata);
        self::assertSame('visible', $mapped->metadata['safe']);

        $withoutRoute = (new SearchResponseMapper())->mapSuggestion(new SearchSuggestion(
            text: 'receipt',
            metadata: ['routeParameters' => 'invalid'],
        ));
        self::assertNull($withoutRoute->routeName);
        self::assertSame([], $withoutRoute->routeParameters);
    }
}
