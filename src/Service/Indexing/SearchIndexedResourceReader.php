<?php

declare(strict_types=1);

namespace App\Searching\Service\Indexing;

use App\Searching\Contract\Indexing\SearchIndexedResourceReaderInterface;
use App\Searching\Entity\SearchIndexedResourceEntity;
use App\Searching\Repository\SearchIndexedResourceRepository;
use App\Searching\ValueObject\Indexing\SearchIndexedResourceCriteria;

/**
 * Defines the search indexed resource reader responsibility within the Searching component runtime and its typed boundaries.
 */
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

    /**
     * Counts the count matching the supplied Searching component criteria.
     */
    public function count(SearchIndexedResourceCriteria $criteria): int
    {
        return $this->repository->countByCriteria($criteria);
    }
}
