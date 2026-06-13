<?php

declare(strict_types=1);

namespace App\Searching\Service\Tuning;

use App\Searching\Entity\SearchSynonymEntity;
use App\Searching\Repository\SearchSynonymRepository;
use App\Searching\ServiceInterface\Tuning\SearchSynonymReaderInterface;
use App\Searching\Value\Tuning\SearchSynonymCriteria;

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

    public function count(SearchSynonymCriteria $criteria): int
    {
        return $this->repository->countByCriteria($criteria);
    }

    public function findOne(int $id): ?SearchSynonymEntity
    {
        return $this->repository->find($id);
    }
}
