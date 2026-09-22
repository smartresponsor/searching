<?php

declare(strict_types=1);

namespace App\Searching\Contract\Tuning;

use App\Searching\Entity\SearchRelevanceProfileEntity;

/**
 * Defines the search relevance profile writer interface responsibility within the Searching component runtime and its typed boundaries.
 */
interface SearchRelevanceProfileWriterInterface
{
    /**
     * @param array<string, mixed> $payload
     */
    public function create(array $payload): SearchRelevanceProfileEntity;

    /**
     * @param array<string, mixed> $payload
     */
    public function update(SearchRelevanceProfileEntity $profile, array $payload): SearchRelevanceProfileEntity;

    /**
     * Deletes the delete through the Searching component mutation boundary.
     */
    public function delete(SearchRelevanceProfileEntity $profile): void;
}
