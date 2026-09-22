<?php

declare(strict_types=1);

namespace App\Searching\Contract\Indexing;

use App\Searching\Entity\SearchReindexJobEntity;

/**
 * Detects already-open reindex jobs so HTTP/CLI callers do not enqueue duplicates.
 */
interface SearchReindexDuplicateGuardInterface
{
    /**
     * Finds the open duplicate through the Searching component read or persistence boundary.
     */
    public function findOpenDuplicate(string $idempotencyKey): ?SearchReindexJobEntity;
}
