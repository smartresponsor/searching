<?php

declare(strict_types=1);

namespace App\Searching\ServiceInterface\Query;

use App\Searching\Entity\SearchQueryLogEntity;
use App\Searching\Value\Query\SearchQueryLogCriteria;

interface SearchQueryLogReaderInterface
{
    /**
     * @return list<SearchQueryLogEntity>
     */
    public function recent(SearchQueryLogCriteria $criteria): array;

    public function count(SearchQueryLogCriteria $criteria): int;
}
