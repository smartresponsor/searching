<?php

declare(strict_types=1);

namespace App\Searching\Service\Indexing;

use App\Searching\Entity\SearchReindexJobEntity;
use App\Searching\Repository\SearchReindexJobRepository;
use App\Searching\ServiceInterface\Indexing\SearchReindexJobReaderInterface;
use App\Searching\Value\Indexing\SearchReindexJobCriteria;

final readonly class SearchReindexJobReader implements SearchReindexJobReaderInterface
{
    public function __construct(private SearchReindexJobRepository $repository)
    {
    }

    /**
     * @return list<SearchReindexJobEntity>
     */
    public function list(SearchReindexJobCriteria $criteria): array
    {
        return $this->repository->findByCriteria($criteria);
    }

    public function count(SearchReindexJobCriteria $criteria): int
    {
        return $this->repository->countByCriteria($criteria);
    }

    public function findOne(string $jobKey): ?SearchReindexJobEntity
    {
        return $this->repository->findOneByJobKey($jobKey);
    }
}
