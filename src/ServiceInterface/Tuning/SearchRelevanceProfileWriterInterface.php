<?php

declare(strict_types=1);

namespace App\Searching\ServiceInterface\Tuning;

use App\Searching\Entity\SearchRelevanceProfileEntity;

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

    public function delete(SearchRelevanceProfileEntity $profile): void;
}
