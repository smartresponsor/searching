<?php

declare(strict_types=1);

namespace App\Searching\Service\Tuning;

use App\Searching\Contract\Tuning\SearchRelevanceProfileReaderInterface;
use App\Searching\Entity\SearchRelevanceProfileEntity;
use App\Searching\Repository\SearchRelevanceProfileRepository;
use App\Searching\Value\Tuning\SearchRelevanceProfileCriteria;

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

    public function count(SearchRelevanceProfileCriteria $criteria): int
    {
        return $this->repository->countByCriteria($criteria);
    }

    public function findOne(int $id): ?SearchRelevanceProfileEntity
    {
        return $this->repository->find($id);
    }
}
