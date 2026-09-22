<?php

declare(strict_types=1);

namespace App\Searching\Service\Tuning;

use App\Searching\Contract\Tuning\SearchRelevanceProfileReaderInterface;
use App\Searching\Entity\SearchRelevanceProfileEntity;
use App\Searching\Repository\SearchRelevanceProfileRepository;
use App\Searching\ValueObject\Tuning\SearchRelevanceProfileCriteria;

/**
 * Defines the search relevance profile reader responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchRelevanceProfileReader implements SearchRelevanceProfileReaderInterface
{
    public function __construct(private SearchRelevanceProfileRepository $repository)
    {
    }

    /**
     * @return list<SearchRelevanceProfileEntity>
     */
    public function find(SearchRelevanceProfileCriteria $criteria): array
    {
        return $this->repository->findByCriteria($criteria);
    }

    /**
     * Counts the count matching the supplied Searching component criteria.
     */
    public function count(SearchRelevanceProfileCriteria $criteria): int
    {
        return $this->repository->countByCriteria($criteria);
    }

    /**
     * Finds the one through the Searching component read or persistence boundary.
     */
    public function findOne(int $id): ?SearchRelevanceProfileEntity
    {
        return $this->repository->find($id);
    }
}
