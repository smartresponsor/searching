<?php

declare(strict_types=1);

namespace App\Searching\Contract\Tuning;

use App\Searching\Entity\SearchSynonymEntity;

/**
 * Defines the search synonym writer interface responsibility within the Searching component runtime and its typed boundaries.
 */
interface SearchSynonymWriterInterface
{
    /**
     * @param array<string, mixed> $payload
     */
    public function create(array $payload): SearchSynonymEntity;

    /**
     * @param array<string, mixed> $payload
     */
    public function update(SearchSynonymEntity $synonym, array $payload): SearchSynonymEntity;

    /**
     * Deletes the delete through the Searching component mutation boundary.
     */
    public function delete(SearchSynonymEntity $synonym): void;
}
