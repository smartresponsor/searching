<?php

declare(strict_types=1);

namespace App\Searching\Tests\Result;

use App\Searching\Service\SearchResponseMapper;
use App\Searching\ValueObject\Result\SearchFacet;
use App\Searching\ValueObject\Result\SearchHighlight;
use App\Searching\ValueObject\Result\SearchResult;
use App\Searching\ValueObject\Result\SearchResultItem;
use App\Searching\ValueObject\Result\SearchSuggestion;
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
        self::assertSame('brand', $result->facets[0]->identifier);
        self::assertSame('brand', $result->facets[0]->nameEntity);
        self::assertSame(['acme' => 2], $result->facets[0]->buckets);
    }

    public function testFacetBucketsAreCanonicalAndDeterministic(): void
    {
        $facet = new SearchFacet(' Brand ', [
            'Zeta' => 2,
            'Beta' => 3,
            'Alpha' => 3,
        ]);

        self::assertSame('brand', $facet->identifier);
        self::assertSame([
            'alpha' => 3,
            'beta' => 3,
            'zeta' => 2,
        ], $facet->buckets);
    }

    public function testInvalidFacetSemanticsAreRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new SearchFacet('brand color', ['alpha' => 1]);
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
