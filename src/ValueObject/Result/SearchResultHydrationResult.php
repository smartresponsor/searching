<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Result;

/**
 * Defines the search result hydration result responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchResultHydrationResult
{
    /**
     * @param list<SearchResultItem>     $items
     * @param list<array<string, mixed>> $droppedItems
     */
    public function __construct(
        public array $items,
        public array $droppedItems,
        public int $originalCount,
    ) {
    }

    /**
     * Executes the hydrated count responsibility defined by the Searching component contract.
     */
    public function hydratedCount(): int
    {
        return count($this->items);
    }

    /**
     * Executes the dropped count responsibility defined by the Searching component contract.
     */
    public function droppedCount(): int
    {
        return count($this->droppedItems);
    }
}
