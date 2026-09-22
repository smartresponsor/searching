<?php

declare(strict_types=1);

namespace App\Searching\Service\Indexing;

use App\Searching\Contract\Indexing\SearchIndexLifecycleRegistrySynchronizerInterface;
use App\Searching\ValueObject\Indexing\SearchIndexLifecycleRegistrySyncResult;
use App\Searching\ValueObject\Provider\SearchIndexLifecycleResult;

final readonly class SearchNullIndexLifecycleRegistrySynchronizer implements SearchIndexLifecycleRegistrySynchronizerInterface
{
    public function sync(string $component, string $resourceType, SearchIndexLifecycleResult $result): SearchIndexLifecycleRegistrySyncResult
    {
        return SearchIndexLifecycleRegistrySyncResult::skipped('Search index registry synchronization is disabled.');
    }
}
