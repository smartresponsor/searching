<?php

declare(strict_types=1);

namespace App\Searching\Service\Security;

use App\Searching\Contract\Security\SearchPermissionCheckerInterface;
use App\Searching\Value\Query\SearchQuery;
use App\Searching\Value\Result\SearchResultItem;
use App\Searching\Value\Security\SearchPermissionDecision;

/**
 * Default final guard for user-facing search results.
 *
 * Search backend filters are treated as an optimization only. This checker keeps
 * the last decision inside the Symfony application boundary, using normalized
 * metadata carried by the SearchResultItem until host-level voters are wired.
 */
final class SearchPermissionChecker implements SearchPermissionCheckerInterface
{
    public function decide(SearchResultItem $item, SearchQuery $query): SearchPermissionDecision
    {
        $visibility = $this->stringMetadata($item, 'visibility');
        if ('public' === $visibility) {
            return SearchPermissionDecision::allow('public_visibility');
        }

        $itemVendorId = $this->stringMetadata($item, 'vendorId') ?? $this->stringMetadata($item, 'vendor_id');
        if (null !== $query->vendorId && null !== $itemVendorId && $itemVendorId !== $query->vendorId) {
            return SearchPermissionDecision::deny('vendor_mismatch', [
                'queryVendorId' => $query->vendorId,
                'itemVendorId' => $itemVendorId,
            ]);
        }

        $ownerId = $this->stringMetadata($item, 'ownerId') ?? $this->stringMetadata($item, 'owner_id');
        if (null !== $ownerId) {
            if (null !== $query->userId && $ownerId === $query->userId) {
                return SearchPermissionDecision::allow('owner_match');
            }

            if ('private' === $visibility) {
                return SearchPermissionDecision::deny('private_owner_mismatch');
            }
        }

        $allowedUserIds = $this->stringListMetadata($item, 'allowedUserIds')
            ?: $this->stringListMetadata($item, 'allowed_user_ids');
        if ([] !== $allowedUserIds) {
            if (null !== $query->userId && in_array($query->userId, $allowedUserIds, true)) {
                return SearchPermissionDecision::allow('allowed_user_match');
            }

            return SearchPermissionDecision::deny('user_not_in_allowed_list');
        }

        $requiredPermissions = $this->stringListMetadata($item, 'requiredPermissions')
            ?: $this->stringListMetadata($item, 'required_permissions')
            ?: $this->stringListMetadata($item, 'permissions');
        if ([] !== $requiredPermissions) {
            $grantedPermissions = $query->userPermissions;
            if ([] === $grantedPermissions) {
                return SearchPermissionDecision::deny('missing_required_permissions', [
                    'requiredPermissions' => $requiredPermissions,
                ]);
            }

            foreach ($requiredPermissions as $permission) {
                if (!in_array($permission, $grantedPermissions, true)) {
                    return SearchPermissionDecision::deny('missing_required_permission', [
                        'requiredPermission' => $permission,
                    ]);
                }
            }

            return SearchPermissionDecision::allow('required_permissions_match');
        }

        if ('private' === $visibility) {
            return SearchPermissionDecision::deny('private_without_positive_match');
        }

        return SearchPermissionDecision::allow('not_restricted');
    }

    private function stringMetadata(SearchResultItem $item, string $key): ?string
    {
        $value = $item->metadata[$key] ?? null;

        return is_string($value) && '' !== $value ? $value : null;
    }

    /**
     * @return list<string>
     */
    private function stringListMetadata(SearchResultItem $item, string $key): array
    {
        $value = $item->metadata[$key] ?? null;
        if (!is_array($value)) {
            return [];
        }

        $strings = [];
        foreach ($value as $itemValue) {
            if (is_string($itemValue) && '' !== $itemValue) {
                $strings[] = $itemValue;
            }
        }

        return $strings;
    }
}
