<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service\Serialization;

use App\Searching\Service\Serialization\SearchResultSerializer;
use App\Searching\Value\Result\SearchResult;
use App\Searching\Value\Result\SearchResultItem;
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

        self::assertSame('invoice', $payload['query']);
        self::assertSame(1, $payload['total']);
        self::assertSame('ordering', $payload['items'][0]['component']);
        self::assertSame('order_show', $payload['items'][0]['routeName']);
    }
}
