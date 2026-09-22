<?php

declare(strict_types=1);

namespace App\Searching\Contract\Tuning;

use App\Searching\Entity\SearchSynonymEntity;
use App\Searching\ValueObject\Tuning\SearchSynonymCriteria;

/**
 * Defines the search synonym reader interface responsibility within the Searching component runtime and its typed boundaries.
 */
interface SearchSynonymReaderInterface
{
    /**
     * @return list<SearchSynonymEntity>
     */
    public function find(SearchSynonymCriteria $criteria): array;

    /**
     * Counts the count matching the supplied Searching component criteria.
     */
    public function count(SearchSynonymCriteria $criteria): int;

    /**
     * Finds the one through the Searching component read or persistence boundary.
     */
    public function findOne(int $id): ?SearchSynonymEntity;
}
