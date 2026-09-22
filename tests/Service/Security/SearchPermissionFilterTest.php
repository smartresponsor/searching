<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service\Security;

use App\Searching\Service\Security\SearchPermissionChecker;
use App\Searching\Service\Security\SearchPermissionFilter;
use App\Searching\ValueObject\Query\SearchQuery;
use App\Searching\ValueObject\Result\SearchResultItem;
use PHPUnit\Framework\TestCase;

final class SearchPermissionFilterTest extends TestCase
{
    public function testItReturnsAllowedItemsAndDeniedSummary(): void
    {
        $filter = new SearchPermissionFilter(new SearchPermissionChecker());
        $result = $filter->filterResult([
            $this->item('1', ['visibility' => 'public']),
            $this->item('2', ['visibility' => 'private', 'ownerId' => 'user-2']),
        ], new SearchQuery('demo', userId: 'user-1'));

        self::assertSame(2, $result->originalCount);
        self::assertSame(1, $result->allowedCount());
        self::assertSame(1, $result->deniedCount());
        self::assertSame('2', $result->deniedItems[0]['resourceId']);
    }

    public function testConvenienceFilterReturnsOnlyAllowedItemsForUser(): void
    {
        $filter = new SearchPermissionFilter(new SearchPermissionChecker());
        $public = $this->item('public', ['visibility' => 'public']);
        $owned = $this->item('owned', ['visibility' => 'private', 'ownerId' => 'user-1']);
        $foreign = $this->item('foreign', ['visibility' => 'private', 'ownerId' => 'user-2']);

        self::assertSame(
            [$public, $owned],
            $filter->filter([$public, $owned, $foreign], 'user-1'),
        );
    }

    /**
     * @param array<string, mixed> $metadata
     */
    private function item(string $id, array $metadata): SearchResultItem
    {
        return new SearchResultItem(
            component: 'ordering',
            resourceType: 'order',
            resourceId: $id,
            title: 'Order '.$id,
            summary: null,
            routeName: 'order_show',
            routeParameters: ['id' => $id],
            metadata: $metadata,
        );
    }
}
