<?php

declare(strict_types=1);

namespace App\Searching\Service\Indexing;

use App\Searching\Contract\Indexing\SearchReindexJobReaderInterface;
use App\Searching\Entity\SearchReindexJobEntity;
use App\Searching\Repository\SearchReindexJobRepository;
use App\Searching\ValueObject\Indexing\SearchReindexJobCriteria;

/**
 * Defines the search reindex job reader responsibility within the Searching component runtime and its typed boundaries.
 */
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

    /**
     * Counts the count matching the supplied Searching component criteria.
     */
    public function count(SearchReindexJobCriteria $criteria): int
    {
        return $this->repository->countByCriteria($criteria);
    }

    /**
     * Finds the one through the Searching component read or persistence boundary.
     */
    public function findOne(string $jobKey): ?SearchReindexJobEntity
    {
        return $this->repository->findOneByJobKey($jobKey);
    }
}
