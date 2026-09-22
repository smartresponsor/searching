<?php

declare(strict_types=1);

namespace App\Searching\Contract\Indexing;

use App\Searching\Entity\SearchIndexedResourceEntity;
use App\Searching\ValueObject\Indexing\SearchIndexedResourceCriteria;

interface SearchIndexedResourceReaderInterface
{
    /**
     * @return list<SearchIndexedResourceEntity>
     */
    public function list(SearchIndexedResourceCriteria $criteria): array;

    public function count(SearchIndexedResourceCriteria $criteria): int;
}
