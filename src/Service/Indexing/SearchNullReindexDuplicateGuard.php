<?php

declare(strict_types=1);

namespace App\Searching\Service\Indexing;

use App\Searching\Contract\Indexing\SearchReindexDuplicateGuardInterface;
use App\Searching\Entity\SearchReindexJobEntity;

/**
 * Defines the search null reindex duplicate guard responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchNullReindexDuplicateGuard implements SearchReindexDuplicateGuardInterface
{
    /**
     * Finds the open duplicate through the Searching component read or persistence boundary.
     */
    public function findOpenDuplicate(string $idempotencyKey): ?SearchReindexJobEntity
    {
        unset($idempotencyKey);

        return null;
    }
}
