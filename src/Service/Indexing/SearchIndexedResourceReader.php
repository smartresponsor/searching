<?php

declare(strict_types=1);

namespace App\Searching\Service\Indexing;

use App\Searching\Contract\Indexing\SearchIndexedResourceReaderInterface;
use App\Searching\Entity\SearchIndexedResourceEntity;
use App\Searching\Repository\SearchIndexedResourceRepository;
use App\Searching\Value\Indexing\SearchIndexedResourceCriteria;

final readonly class SearchIndexedResourceReader implements SearchIndexedResourceReaderInterface
{
    public function __construct(private SearchIndexedResourceRepository $repository)
    {
    }

    /**
     * @return list<SearchIndexedResourceEntity>
     */
    public function list(SearchIndexedResourceCriteria $criteria): array
    {
        return $this->repository->findByCriteria($criteria);
    }

    public function count(SearchIndexedResourceCriteria $criteria): int
    {
        return $this->repository->countByCriteria($criteria);
    }
}
