<?php

declare(strict_types=1);

namespace App\Searching\Contract\Indexing;

use App\Searching\Entity\SearchReindexJobEntity;
use App\Searching\Value\Indexing\SearchReindexJobCriteria;

interface SearchReindexJobReaderInterface
{
    /**
     * @return list<SearchReindexJobEntity>
     */
    public function list(SearchReindexJobCriteria $criteria): array;

    public function count(SearchReindexJobCriteria $criteria): int;

    public function findOne(string $jobKey): ?SearchReindexJobEntity;
}
