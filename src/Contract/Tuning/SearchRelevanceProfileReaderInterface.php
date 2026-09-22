<?php

declare(strict_types=1);

namespace App\Searching\Contract\Tuning;

use App\Searching\Entity\SearchRelevanceProfileEntity;
use App\Searching\ValueObject\Tuning\SearchRelevanceProfileCriteria;

/**
 * Defines the search relevance profile reader interface responsibility within the Searching component runtime and its typed boundaries.
 */
interface SearchRelevanceProfileReaderInterface
{
    /**
     * @return list<SearchRelevanceProfileEntity>
     */
    public function find(SearchRelevanceProfileCriteria $criteria): array;

    /**
     * Counts the count matching the supplied Searching component criteria.
     */
    public function count(SearchRelevanceProfileCriteria $criteria): int;

    /**
     * Finds the one through the Searching component read or persistence boundary.
     */
    public function findOne(int $id): ?SearchRelevanceProfileEntity;
}
