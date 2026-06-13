<?php

declare(strict_types=1);

namespace App\Searching\ServiceInterface\Indexing;

use App\Searching\Value\Indexing\SearchIndexedResourceCriteria;

interface SearchIndexedResourceReaderInterface
{
    /**
     * @return list<SearchIndexedResourceEntity>
     */
    public function list(SearchIndexedResourceCriteria $criteria): array;

    public function count(SearchIndexedResourceCriteria $criteria): int;
}
