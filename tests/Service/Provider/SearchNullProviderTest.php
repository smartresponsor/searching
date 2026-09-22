<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service\Provider;

use App\Searching\Provider\Backend\SearchNullProvider;
use App\Searching\ValueObject\Document\SearchDocument;
use App\Searching\ValueObject\Query\SearchQuery;
use App\Searching\ValueObject\Query\SearchSuggestionQuery;
use PHPUnit\Framework\TestCase;

final class SearchNullProviderTest extends TestCase
{
    public function testSearchReturnsEmptyResult(): void
    {
        $provider = new SearchNullProvider();
        $result = $provider->search(new SearchQuery('anything'));

        self::assertSame(0, $result->total);
        self::assertSame([], $result->items);
    }

    public function testCompleteNullProviderContractIsSafeNoOp(): void
    {
        $provider = new SearchNullProvider();
        $document = new SearchDocument(
            component: 'ordering', resourceType: 'order', resourceId: '42', title: 'Order 42',
            summary: null, body: null, keywords: [], facets: [], permissions: [], locale: null,
            vendorId: null, ownerId: null, routeName: 'order_show', routeParameters: ['id' => 42],
            updatedAt: new \DateTimeImmutable('2026-09-15T12:00:00+00:00'),
        );

        $provider->index($document);
        $provider->bulkIndex([$document]);
        $provider->delete('ordering', 'order', '42');

        self::assertSame([], $provider->suggest(new SearchSuggestionQuery('ord')));
        $status = $provider->getStatus();
        self::assertSame('null', $status->nameEntity);
        self::assertTrue($status->available);
        self::assertSame('disabled', $status->status);
        self::addToAssertionCount(3);
    }
}
