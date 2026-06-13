<?php

declare(strict_types=1);

namespace App\Searching\ServiceInterface\Indexing;

use App\Searching\Entity\SearchIndexEntity;
use App\Searching\Value\Indexing\SearchIndexCriteria;

interface SearchIndexReaderInterface
{
    /**
     * @return list<SearchIndexEntity>
     */
    public function list(SearchIndexCriteria $criteria): array;

    public function count(SearchIndexCriteria $criteria): int;

    public function findOne(string $provider, string $component, string $resourceType): ?SearchIndexEntity;
}
