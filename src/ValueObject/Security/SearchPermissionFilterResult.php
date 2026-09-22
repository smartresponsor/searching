<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Security;

use App\Searching\ValueObject\Result\SearchResultItem;

/**
 * Defines the search permission filter result responsibility within the Searching component runtime and its typed boundaries.
 */
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

    /**
     * Executes the denied count responsibility defined by the Searching component contract.
     */
    public function deniedCount(): int
    {
        return count($this->deniedItems);
    }

    /**
     * Executes the allowed count responsibility defined by the Searching component contract.
     */
    public function allowedCount(): int
    {
        return count($this->allowedItems);
    }
}
