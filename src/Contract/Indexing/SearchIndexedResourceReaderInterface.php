<?php

declare(strict_types=1);

namespace App\Searching\Contract\Indexing;

use App\Searching\Entity\SearchIndexedResourceEntity;
use App\Searching\ValueObject\Indexing\SearchIndexedResourceCriteria;

/**
 * Defines the search indexed resource reader interface responsibility within the Searching component runtime and its typed boundaries.
 */
interface SearchIndexedResourceReaderInterface
{
    /**
     * @return list<SearchIndexedResourceEntity>
     */
    public function list(SearchIndexedResourceCriteria $criteria): array;

    /**
     * Counts the count matching the supplied Searching component criteria.
     */
    public function count(SearchIndexedResourceCriteria $criteria): int;
}
