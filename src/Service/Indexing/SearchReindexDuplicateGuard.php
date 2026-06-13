<?php

declare(strict_types=1);

namespace App\Searching\Service\Indexing;

use App\Searching\Entity\SearchReindexJobEntity;
use App\Searching\Repository\SearchReindexJobRepository;
use App\Searching\ServiceInterface\Indexing\SearchReindexDuplicateGuardInterface;

final readonly class SearchReindexDuplicateGuard implements SearchReindexDuplicateGuardInterface
{
    public function __construct(private SearchReindexJobRepository $repository)
    {
    }

    public function findOpenDuplicate(string $idempotencyKey): ?SearchReindexJobEntity
    {
        if ('' === $idempotencyKey) {
            return null;
        }

        return $this->repository->findOpenByIdempotencyKey($idempotencyKey);
    }
}
