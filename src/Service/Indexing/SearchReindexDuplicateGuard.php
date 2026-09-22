<?php

declare(strict_types=1);

namespace App\Searching\Service\Indexing;

use App\Searching\Contract\Indexing\SearchReindexDuplicateGuardInterface;
use App\Searching\Entity\SearchReindexJobEntity;
use App\Searching\Repository\SearchReindexJobRepository;

/**
 * Defines the search reindex duplicate guard responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchReindexDuplicateGuard implements SearchReindexDuplicateGuardInterface
{
    public function __construct(private SearchReindexJobRepository $repository)
    {
    }

    /**
     * Finds the open duplicate through the Searching component read or persistence boundary.
     */
    public function findOpenDuplicate(string $idempotencyKey): ?SearchReindexJobEntity
    {
        if ('' === $idempotencyKey) {
            return null;
        }

        return $this->repository->findOpenByIdempotencyKey($idempotencyKey);
    }
}
