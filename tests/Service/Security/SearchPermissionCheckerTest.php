<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service\Security;

use App\Searching\Service\Security\SearchPermissionChecker;
use App\Searching\Value\Query\SearchQuery;
use App\Searching\Value\Result\SearchResultItem;
use PHPUnit\Framework\TestCase;

final class SearchPermissionCheckerTest extends TestCase
{
    public function testItAllowsPublicResult(): void
    {
        $checker = new SearchPermissionChecker();
        $decision = $checker->decide($this->item(['visibility' => 'public']), new SearchQuery('demo'));

        self::assertTrue($decision->allowed);
        self::assertSame('public_visibility', $decision->reason);
    }

    public function testItDeniesTenantMismatch(): void
    {
        $checker = new SearchPermissionChecker();
        $decision = $checker->decide($this->item(['tenantId' => 'tenant-b']), new SearchQuery('demo', tenantId: 'tenant-a'));

        self::assertFalse($decision->allowed);
        self::assertSame('tenant_mismatch', $decision->reason);
    }

    public function testItRequiresOwnerMatchForPrivateResults(): void
    {
        $checker = new SearchPermissionChecker();

        self::assertTrue($checker->decide($this->item([
            'visibility' => 'private',
            'ownerId' => 'user-1',
        ]), new SearchQuery('demo', userId: 'user-1'))->allowed);

        self::assertFalse($checker->decide($this->item([
            'visibility' => 'private',
            'ownerId' => 'user-1',
        ]), new SearchQuery('demo', userId: 'user-2'))->allowed);
    }

    public function testItRequiresDeclaredPermissions(): void
    {
        $checker = new SearchPermissionChecker();
        $item = $this->item(['requiredPermissions' => ['order.view']]);

        self::assertFalse($checker->decide($item, new SearchQuery('demo'))->allowed);
        self::assertTrue($checker->decide($item, new SearchQuery('demo', userPermissions: ['order.view']))->allowed);
    }

    public function testItHandlesAllowedUsersAliasesAndPrivateFallbacks(): void
    {
        $checker = new SearchPermissionChecker();

        self::assertSame('allowed_user_match', $checker->decide(
            $this->item(['allowed_user_ids' => ['user-2', '', 7]]),
            new SearchQuery('demo', userId: 'user-2'),
        )->reason);
        self::assertSame('user_not_in_allowed_list', $checker->decide(
            $this->item(['allowedUserIds' => ['user-2']]),
            new SearchQuery('demo', userId: 'user-3'),
        )->reason);
        self::assertSame('private_without_positive_match', $checker->decide(
            $this->item(['visibility' => 'private']),
            new SearchQuery('demo', userId: 'user-3'),
        )->reason);
        self::assertSame('not_restricted', $checker->decide(
            $this->item(['tenant_id' => 'tenant-a', 'visibility' => 5]),
            new SearchQuery('demo', tenantId: 'tenant-a'),
        )->reason);
    }

    public function testItDeniesOneMissingPermissionAndAcceptsLegacyPermissionAlias(): void
    {
        $checker = new SearchPermissionChecker();
        $missing = $checker->decide(
            $this->item(['required_permissions' => ['order.view', 'order.export']]),
            new SearchQuery('demo', userPermissions: ['order.view']),
        );
        self::assertFalse($missing->allowed);
        self::assertSame('missing_required_permission', $missing->reason);

        $allowed = $checker->decide(
            $this->item(['permissions' => ['order.view', '', 99]]),
            new SearchQuery('demo', userPermissions: ['order.view']),
        );
        self::assertTrue($allowed->allowed);
        self::assertSame('required_permissions_match', $allowed->reason);
    }

    /**
     * @param array<string, mixed> $metadata
     */
    private function item(array $metadata): SearchResultItem
    {
        return new SearchResultItem(
            component: 'ordering',
            resourceType: 'order',
            resourceId: '42',
            title: 'Order 42',
            summary: null,
            routeName: 'order_show',
            routeParameters: ['id' => 42],
            metadata: $metadata,
        );
    }
}
