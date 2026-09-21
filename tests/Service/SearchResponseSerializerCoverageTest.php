<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service;

use App\Searching\Service\SearchResponseSerializer;
use App\Searching\Value\Provider\SearchCapability;
use App\Searching\Value\Result\SearchFacetResponse;
use App\Searching\Value\Result\SearchHighlightResponse;
use App\Searching\Value\Result\SearchResponse;
use App\Searching\Value\Result\SearchResponseItem;
use App\Searching\Value\Result\SearchSuggestionResponse;
use PHPUnit\Framework\TestCase;

final class SearchResponseSerializerCoverageTest extends TestCase
{
    public function testItSerializesCompleteResponseSurface(): void
    {
        $serializer = new SearchResponseSerializer();
        $response = new SearchResponse(
            query: 'invoice',
            total: 1,
            page: 1,
            limit: 20,
            items: [new SearchResponseItem(
                component: 'ordering',
                resourceType: 'order',
                resourceId: '42',
                title: 'Invoice 42',
                summary: 'Paid invoice',
                routeName: 'order_show',
                routeParameters: ['id' => 42],
                score: 0.9,
                highlights: [new SearchHighlightResponse('title', ['<em>Invoice</em> 42'])],
                metadata: ['safe' => true],
            )],
            facets: [new SearchFacetResponse('status', ['paid' => 1])],
            suggestions: [new SearchSuggestionResponse(
                text: 'invoice 42',
                score: 0.8,
                component: 'ordering',
                resourceType: 'order',
                resourceId: '42',
                routeName: 'order_show',
                routeParameters: ['id' => 42],
                metadata: ['source' => 'surface'],
            )],
            metadata: ['degraded' => false],
        );

        $payload = $serializer->serializeResult($response);

        self::assertSame('invoice', $payload['query']);
        self::assertSame(1, $payload['total']);
        self::assertSame(1, $payload['page']);
        self::assertSame(20, $payload['limit']);
        self::assertSame(['degraded' => false], $payload['metadata']);
    }

    public function testItSerializesHelpersAndFiltersEmptySuggestionValues(): void
    {
        $serializer = new SearchResponseSerializer();

        self::assertSame(
            ['identifier' => 'brand', 'nameEntity' => 'brand', 'buckets' => ['acme' => 2]],
            $serializer->serializeFacet(new SearchFacetResponse(' Brand ', ['Acme' => 2])),
        );
        self::assertSame(
            ['field' => 'title', 'fragments' => ['Phone']],
            $serializer->serializeHighlight(new SearchHighlightResponse('title', ['Phone'])),
        );
        self::assertSame([
            'text' => 'phone',
            'score' => 0.5,
        ], $serializer->serializeSuggestion(new SearchSuggestionResponse(text: 'phone', score: 0.5)));

        self::assertSame([
            'enabled' => true,
            'providerName' => 'opensearch',
            'supportedFeatures' => ['search', 'suggest'],
            'supportedComponents' => ['cataloging'],
            'supportedResourceTypes' => ['product'],
            'metadata' => ['region' => 'local'],
        ], $serializer->serializeCapability(new SearchCapability(
            enabled: true,
            providerName: 'opensearch',
            supportedFeatures: ['search', 'suggest'],
            supportedComponents: ['cataloging'],
            supportedResourceTypes: ['product'],
            metadata: ['region' => 'local'],
        )));
    }
}
