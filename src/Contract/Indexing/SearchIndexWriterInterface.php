<?php

declare(strict_types=1);

namespace App\Searching\Contract\Indexing;

use App\Searching\Entity\SearchIndexEntity;

/**
 * Defines the search index writer interface responsibility within the Searching component runtime and its typed boundaries.
 */
interface SearchIndexWriterInterface
{
    /**
     * @param array<string, mixed> $input
     */
    public function upsert(array $input): SearchIndexEntity;

    /**
     * @param array<string, mixed> $input
     */
    public function update(SearchIndexEntity $index, array $input): SearchIndexEntity;

    /**
     * Deletes the delete through the Searching component mutation boundary.
     */
    public function delete(SearchIndexEntity $index): void;
}
