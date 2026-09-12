<?php

declare(strict_types=1);

namespace App\Searching\Service\Indexing;

use App\Searching\Contract\Indexing\SearchReindexDuplicateGuardInterface;
use App\Searching\Entity\SearchReindexJobEntity;

final readonly class SearchNullReindexDuplicateGuard implements SearchReindexDuplicateGuardInterface
{
    public function findOpenDuplicate(string $idempotencyKey): ?SearchReindexJobEntity
    {
        unset($idempotencyKey);

        return null;
    }
}
