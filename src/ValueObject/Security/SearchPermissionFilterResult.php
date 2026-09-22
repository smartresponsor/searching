<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Security;

use App\Searching\ValueObject\Result\SearchResultItem;

final readonly class SearchPermissionFilterResult
{
    /**
     * @param list<SearchResultItem>                                                                   $allowedItems
     * @param list<array{component: string, resourceType: string, resourceId: string, reason: string}> $deniedItems
     */
    public function __construct(
        public array $allowedItems,
        public array $deniedItems,
        public int $originalCount,
    ) {
    }

    public function deniedCount(): int
    {
        return count($this->deniedItems);
    }

    public function allowedCount(): int
    {
        return count($this->allowedItems);
    }
}
