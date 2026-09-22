<?php

declare(strict_types=1);

namespace App\Searching\Contract\Tuning;

use App\Searching\Entity\SearchSynonymEntity;
use App\Searching\ValueObject\Tuning\SearchSynonymCriteria;

interface SearchSynonymReaderInterface
{
    /**
     * @return list<SearchSynonymEntity>
     */
    public function find(SearchSynonymCriteria $criteria): array;

    public function count(SearchSynonymCriteria $criteria): int;

    public function findOne(int $id): ?SearchSynonymEntity;
}
