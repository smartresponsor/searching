<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Result;

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

    public function hydratedCount(): int
    {
        return count($this->items);
    }

    public function droppedCount(): int
    {
        return count($this->droppedItems);
    }
}
