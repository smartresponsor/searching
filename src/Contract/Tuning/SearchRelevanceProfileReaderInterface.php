<?php

declare(strict_types=1);

namespace App\Searching\Contract\Tuning;

use App\Searching\Entity\SearchRelevanceProfileEntity;
use App\Searching\ValueObject\Tuning\SearchRelevanceProfileCriteria;

interface SearchRelevanceProfileReaderInterface
{
    /**
     * @return list<SearchRelevanceProfileEntity>
     */
    public function find(SearchRelevanceProfileCriteria $criteria): array;

    public function count(SearchRelevanceProfileCriteria $criteria): int;

    public function findOne(int $id): ?SearchRelevanceProfileEntity;
}
