<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service\Query;

use App\Searching\Service\Query\SearchResultHydrator;
use App\Searching\ServiceInterface\Producer\SearchResultItemHydratorInterface;
use App\Searching\Value\Result\SearchResultItem;
use PHPUnit\Framework\TestCase;

final class SearchResultHydratorTest extends TestCase
{
    public function testHydrateKeepsItemsWithoutProducerHydrator(): void
    {
        $hydrator = new SearchResultHydrator();
        $item = $this->item('cataloging', 'product', '1');

        $result = $hydrator->hydrate([$item]);

        self::assertSame([$item], $result->items);
        self::assertSame(0, $result->droppedCount());
    }

    public function testHydrateDropsStaleItemsWhenProducerReturnsNull(): void
    {
        $hydrator = new SearchResultHydrator();
        $hydrator->add(new DroppingHydrator());

        $result = $hydrator->hydrate([$this->item('cataloging', 'product', '1')]);

        self::assertSame([], $result->items);
        self::assertSame(1, $result->droppedCount());
        self::assertSame('source_missing_or_stale', $result->droppedItems[0]['reason']);
    }

    private function item(string $component, string $resourceType, string $resourceId): SearchResultItem
    {
        return new SearchResultItem(
            component: $component,
            resourceType: $resourceType,
            resourceId: $resourceId,
            title: 'Result',
            summary: null,
            routeName: 'result_show',
            routeParameters: ['id' => $resourceId],
        );
    }
}

final class DroppingHydrator implements SearchResultItemHydratorInterface
{
    public function getSearchableComponentName(): string
    {
        return 'cataloging';
    }

    public function getSearchableResourceName(): string
    {
        return 'product';
    }

    public function hydrateSearchResultItem(SearchResultItem $item): ?SearchResultItem
    {
        return null;
    }
}
