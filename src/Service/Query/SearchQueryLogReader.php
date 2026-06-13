<?php

declare(strict_types=1);

namespace App\Searching\Service\Query;

use App\Searching\Entity\SearchQueryLogEntity;
use App\Searching\Repository\SearchQueryLogRepository;
use App\Searching\ServiceInterface\Query\SearchQueryLogReaderInterface;
use App\Searching\Value\Query\SearchQueryLogCriteria;

final readonly class SearchQueryLogReader implements SearchQueryLogReaderInterface
{
    public function __construct(
        private SearchQueryLogRepository $repository,
    ) {
    }

    /**
     * @return list<SearchQueryLogEntity>
     */
    public function recent(SearchQueryLogCriteria $criteria): array
    {
        return $this->repository->findByCriteria($criteria);
    }

    public function count(SearchQueryLogCriteria $criteria): int
    {
        return $this->repository->countByCriteria($criteria);
    }
}
