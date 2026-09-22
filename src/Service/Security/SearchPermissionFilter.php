<?php

declare(strict_types=1);

namespace App\Searching\Service\Security;

use App\Searching\Contract\Security\SearchPermissionCheckerInterface;
use App\Searching\Contract\Security\SearchPermissionFilterInterface;
use App\Searching\ValueObject\Query\SearchQuery;
use App\Searching\ValueObject\Result\SearchResultItem;
use App\Searching\ValueObject\Security\SearchPermissionFilterResult;

final readonly class SearchPermissionFilter implements SearchPermissionFilterInterface
{
    public function __construct(
        private SearchPermissionCheckerInterface $permissionChecker,
    ) {
    }

    /**
     * @param list<SearchResultItem> $items
     *
     * @return list<SearchResultItem>
     */
    public function filter(array $items, ?string $userId = null): array
    {
        return $this->filterResult($items, new SearchQuery(query: '', userId: $userId))->allowedItems;
    }

    /**
     * @param list<SearchResultItem> $items
     */
    public function filterResult(array $items, SearchQuery $query): SearchPermissionFilterResult
    {
        $allowedItems = [];
        $deniedItems = [];

        foreach ($items as $item) {
            $decision = $this->permissionChecker->decide($item, $query);
            if ($decision->allowed) {
                $allowedItems[] = $item;
                continue;
            }

            $deniedItems[] = [
                'component' => $item->component,
                'resourceType' => $item->resourceType,
                'resourceId' => $item->resourceId,
                'reason' => $decision->reason,
            ];
        }

        return new SearchPermissionFilterResult($allowedItems, $deniedItems, count($items));
    }
}
