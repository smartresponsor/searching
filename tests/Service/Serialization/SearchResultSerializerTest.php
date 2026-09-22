<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service\Serialization;

use App\Searching\Service\Serialization\SearchResultSerializer;
use App\Searching\ValueObject\Result\SearchFacet;
use App\Searching\ValueObject\Result\SearchHighlight;
use App\Searching\ValueObject\Result\SearchResult;
use App\Searching\ValueObject\Result\SearchResultItem;
use App\Searching\ValueObject\Result\SearchSuggestion;
use PHPUnit\Framework\TestCase;

final class SearchResultSerializerTest extends TestCase
{
    public function testItSerializesSearchResult(): void
    {
        $serializer = new SearchResultSerializer();
        $payload = $serializer->serialize(new SearchResult(
            query: 'invoice',
            total: 1,
            page: 1,
            limit: 20,
            items: [new SearchResultItem(
                component: 'ordering',
                resourceType: 'order',
                resourceId: '42',
                title: 'Invoice 42',
                summary: null,
                routeName: 'order_show',
                routeParameters: ['id' => 42],
            )],
        ));
        /** @var array{query: string, total: int, items: list<array{component: string, routeName: string}>} $payload */
        self::assertSame('invoice', $payload['query']);
        self::assertSame(1, $payload['total']);
        self::assertSame('ordering', $payload['items'][0]['component']);
        self::assertSame('order_show', $payload['items'][0]['routeName']);
    }

    public function testItSerializesHelperValuesAndFiltersEmptySuggestionFields(): void
    {
        $serializer = new SearchResultSerializer();

        self::assertSame(
            ['nameEntity' => 'status', 'buckets' => ['open' => 3]],
            $serializer->serializeFacet(new SearchFacet('status', ['open' => 3])),
        );
        self::assertSame(
            ['field' => 'title', 'fragments' => ['<em>Invoice</em> 42']],
            $serializer->serializeHighlight(new SearchHighlight('title', ['<em>Invoice</em> 42'])),
        );

        self::assertSame([
            'text' => 'invoice',
            'score' => 1.5,
            'component' => 'ordering',
        ], $serializer->serializeSuggestion(new SearchSuggestion(
            text: 'invoice',
            score: 1.5,
            component: 'ordering',
            metadata: [],
        )));

        $item = $serializer->serializeItem(new SearchResultItem(
            component: 'ordering',
            resourceType: 'order',
            resourceId: '42',
            title: 'Invoice 42',
            summary: null,
            routeName: 'order_show',
            routeParameters: ['id' => 42],
            highlights: [new SearchHighlight('title', ['Invoice 42'])],
        ));
        /** @var array{highlights: list<array{field: string}>} $item */
        self::assertSame('title', $item['highlights'][0]['field']);
    }
}
