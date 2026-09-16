<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service\Query;

use App\Searching\Contract\Producer\SearchResultItemHydratorInterface;
use App\Searching\Service\Query\SearchResultHydrator;
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

    public function testHydrateRefreshesMatchedItemAndIsolatesProducerFailure(): void
    {
        $hydrator = new SearchResultHydrator();
        $hydrator->add(new RefreshingHydrator());
        $hydrator->add(new ThrowingHydrator());

        $result = $hydrator->hydrate([
            $this->item('CATALOGING', 'PRODUCT', '1'),
            $this->item('messaging', 'message', '2'),
        ]);

        self::assertSame(2, $result->originalCount);
        self::assertSame(1, $result->hydratedCount());
        self::assertSame('Refreshed', $result->items[0]->title);
        self::assertSame(1, $result->droppedCount());
        self::assertSame('hydration_failed', $result->droppedItems[0]['reason']);
        self::assertSame(\RuntimeException::class, $result->droppedItems[0]['error_class']);
        self::assertSame('hydrate failed', $result->droppedItems[0]['error_message']);
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

final class RefreshingHydrator implements SearchResultItemHydratorInterface
{
    public function getSearchableComponentName(): string
    {
        return 'cataloging';
    }

    public function getSearchableResourceName(): string
    {
        return 'product';
    }

    public function hydrateSearchResultItem(SearchResultItem $item): SearchResultItem
    {
        return new SearchResultItem(
            component: strtolower($item->component),
            resourceType: strtolower($item->resourceType),
            resourceId: $item->resourceId,
            title: 'Refreshed',
            summary: $item->summary,
            routeName: $item->routeName,
            routeParameters: $item->routeParameters,
            metadata: $item->metadata,
        );
    }
}

final class ThrowingHydrator implements SearchResultItemHydratorInterface
{
    public function getSearchableComponentName(): string
    {
        return 'messaging';
    }

    public function getSearchableResourceName(): string
    {
        return 'message';
    }

    public function hydrateSearchResultItem(SearchResultItem $item): ?SearchResultItem
    {
        throw new \RuntimeException('hydrate failed');
    }
}
