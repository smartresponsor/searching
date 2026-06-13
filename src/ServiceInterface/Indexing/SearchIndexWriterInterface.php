<?php

declare(strict_types=1);

namespace App\Searching\ServiceInterface\Indexing;

use App\Searching\Entity\SearchIndexEntity;

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

    public function delete(SearchIndexEntity $index): void;
}
