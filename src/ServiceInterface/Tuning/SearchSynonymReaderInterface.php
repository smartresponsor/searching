<?php

declare(strict_types=1);

namespace App\Searching\ServiceInterface\Tuning;

use App\Searching\Entity\SearchSynonymEntity;
use App\Searching\Value\Tuning\SearchSynonymCriteria;

interface SearchSynonymReaderInterface
{
    /**
     * @return list<SearchSynonymEntity>
     */
    public function find(SearchSynonymCriteria $criteria): array;

    public function count(SearchSynonymCriteria $criteria): int;

    public function findOne(int $id): ?SearchSynonymEntity;
}
