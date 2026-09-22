<?php

declare(strict_types=1);

namespace App\Searching\Service\Tuning;

use App\Searching\Contract\Tuning\SearchSynonymReaderInterface;
use App\Searching\Entity\SearchSynonymEntity;
use App\Searching\Repository\SearchSynonymRepository;
use App\Searching\ValueObject\Tuning\SearchSynonymCriteria;

/**
 * Defines the search synonym reader responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchSynonymReader implements SearchSynonymReaderInterface
{
    public function __construct(private SearchSynonymRepository $repository)
    {
    }

    /**
     * @return list<SearchSynonymEntity>
     */
    public function find(SearchSynonymCriteria $criteria): array
    {
        return $this->repository->findByCriteria($criteria);
    }

    /**
     * Counts the count matching the supplied Searching component criteria.
     */
    public function count(SearchSynonymCriteria $criteria): int
    {
        return $this->repository->countByCriteria($criteria);
    }

    /**
     * Finds the one through the Searching component read or persistence boundary.
     */
    public function findOne(int $id): ?SearchSynonymEntity
    {
        return $this->repository->find($id);
    }
}
