<?php

declare(strict_types=1);

namespace App\Searching\Value\Provider;

/**
 * @implements \IteratorAggregate<int, SearchBulkOperation>
 */
final readonly class SearchBulkOperationSet implements \IteratorAggregate, \Countable
{
    /**
     * @param list<SearchBulkOperation> $operations
     */
    public function __construct(public array $operations)
    {
    }

    public function count(): int
    {
        return count($this->operations);
    }

    public function getIterator(): \Traversable
    {
        yield from $this->operations;
    }

    /**
     * @return array<string, list<SearchBulkOperation>>
     */
    public function groupedByIndex(): array
    {
        $groups = [];

        foreach ($this->operations as $operation) {
            $groups[$operation->indexName][] = $operation;
        }

        return $groups;
    }
}
