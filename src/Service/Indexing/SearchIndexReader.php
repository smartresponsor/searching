<?php

declare(strict_types=1);

namespace App\Searching\Service\Indexing;

use App\Searching\Contract\Indexing\SearchIndexReaderInterface;
use App\Searching\Entity\SearchIndexEntity;
use App\Searching\Repository\SearchIndexRepository;
use App\Searching\ValueObject\Indexing\SearchIndexCriteria;

/**
 * Defines the search index reader responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchIndexReader implements SearchIndexReaderInterface
{
    public function __construct(private SearchIndexRepository $repository)
    {
    }

    /**
     * @return list<SearchIndexEntity>
     */
    public function list(SearchIndexCriteria $criteria): array
    {
        return $this->repository->findByCriteria($criteria);
    }

    /**
     * Counts the count matching the supplied Searching component criteria.
     */
    public function count(SearchIndexCriteria $criteria): int
    {
        return $this->repository->countByCriteria($criteria);
    }

    /**
     * Finds the one through the Searching component read or persistence boundary.
     */
    public function findOne(string $provider, string $component, string $resourceType): ?SearchIndexEntity
    {
        return $this->repository->findOneByIdentity($provider, $component, $resourceType);
    }
}
