<?php

declare(strict_types=1);

namespace App\Searching\Contract\Indexing;

use App\Searching\Entity\SearchIndexEntity;
use App\Searching\ValueObject\Indexing\SearchIndexCriteria;

/**
 * Defines the search index reader interface responsibility within the Searching component runtime and its typed boundaries.
 */
interface SearchIndexReaderInterface
{
    /**
     * @return list<SearchIndexEntity>
     */
    public function list(SearchIndexCriteria $criteria): array;

    /**
     * Counts the count matching the supplied Searching component criteria.
     */
    public function count(SearchIndexCriteria $criteria): int;

    /**
     * Finds the one through the Searching component read or persistence boundary.
     */
    public function findOne(string $provider, string $component, string $resourceType): ?SearchIndexEntity;
}
