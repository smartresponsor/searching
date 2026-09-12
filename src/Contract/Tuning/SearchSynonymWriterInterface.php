<?php

declare(strict_types=1);

namespace App\Searching\Contract\Tuning;

use App\Searching\Entity\SearchSynonymEntity;

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

    public function delete(SearchSynonymEntity $synonym): void;
}
