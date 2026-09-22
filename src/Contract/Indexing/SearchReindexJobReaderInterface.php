<?php

declare(strict_types=1);

namespace App\Searching\Contract\Indexing;

use App\Searching\Entity\SearchReindexJobEntity;
use App\Searching\ValueObject\Indexing\SearchReindexJobCriteria;

/**
 * Defines the search reindex job reader interface responsibility within the Searching component runtime and its typed boundaries.
 */
interface SearchReindexJobReaderInterface
{
    /**
     * @return list<SearchReindexJobEntity>
     */
    public function list(SearchReindexJobCriteria $criteria): array;

    /**
     * Counts the count matching the supplied Searching component criteria.
     */
    public function count(SearchReindexJobCriteria $criteria): int;

    /**
     * Finds the one through the Searching component read or persistence boundary.
     */
    public function findOne(string $jobKey): ?SearchReindexJobEntity;
}
