<?php

declare(strict_types=1);

namespace App\Searching\Contract\Query;

use App\Searching\Entity\SearchQueryLogEntity;
use App\Searching\ValueObject\Query\SearchQueryLogCriteria;

/**
 * Defines the search query log reader interface responsibility within the Searching component runtime and its typed boundaries.
 */
interface SearchQueryLogReaderInterface
{
    /**
     * @return list<SearchQueryLogEntity>
     */
    public function recent(SearchQueryLogCriteria $criteria): array;

    /**
     * Counts the count matching the supplied Searching component criteria.
     */
    public function count(SearchQueryLogCriteria $criteria): int;
}
