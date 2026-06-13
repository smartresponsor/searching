<?php

declare(strict_types=1);

namespace App\Searching\Service\Indexing;

use App\Searching\ServiceInterface\Indexing\SearchIndexLifecycleRegistrySynchronizerInterface;
use App\Searching\Value\Indexing\SearchIndexLifecycleRegistrySyncResult;
use App\Searching\Value\Provider\SearchIndexLifecycleResult;

final readonly class NullSearchIndexLifecycleRegistrySynchronizer implements SearchIndexLifecycleRegistrySynchronizerInterface
{
    public function sync(string $component, string $resourceType, SearchIndexLifecycleResult $result): SearchIndexLifecycleRegistrySyncResult
    {
        return SearchIndexLifecycleRegistrySyncResult::skipped('Search index registry synchronization is disabled.');
    }
}
