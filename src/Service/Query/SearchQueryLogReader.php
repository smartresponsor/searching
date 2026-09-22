<?php

declare(strict_types=1);

namespace App\Searching\Service\Query;

use App\Searching\Contract\Query\SearchQueryLogReaderInterface;
use App\Searching\Entity\SearchQueryLogEntity;
use App\Searching\Repository\SearchQueryLogRepository;
use App\Searching\ValueObject\Query\SearchQueryLogCriteria;

/**
 * Defines the search query log reader responsibility within the Searching component runtime and its typed boundaries.
 */
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

    /**
     * Counts the count matching the supplied Searching component criteria.
     */
    public function count(SearchQueryLogCriteria $criteria): int
    {
        return $this->repository->countByCriteria($criteria);
    }
}
