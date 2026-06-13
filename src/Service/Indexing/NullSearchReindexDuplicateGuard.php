<?php

declare(strict_types=1);

namespace App\Searching\Service\Indexing;

use App\Searching\Entity\SearchReindexJobEntity;
use App\Searching\ServiceInterface\Indexing\SearchReindexDuplicateGuardInterface;

final readonly class NullSearchReindexDuplicateGuard implements SearchReindexDuplicateGuardInterface
{
    public function findOpenDuplicate(string $idempotencyKey): ?SearchReindexJobEntity
    {
        unset($idempotencyKey);

        return null;
    }
}
