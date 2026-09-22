<?php

declare(strict_types=1);

namespace App\Searching\Service\Indexing;

use App\Searching\Contract\Indexing\SearchIndexLifecycleRegistrySynchronizerInterface;
use App\Searching\ValueObject\Indexing\SearchIndexLifecycleRegistrySyncResult;
use App\Searching\ValueObject\Provider\SearchIndexLifecycleResult;

/**
 * Defines the search null index lifecycle registry synchronizer responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchNullIndexLifecycleRegistrySynchronizer implements SearchIndexLifecycleRegistrySynchronizerInterface
{
    /**
     * Executes the sync responsibility defined by the Searching component contract.
     */
    public function sync(string $component, string $resourceType, SearchIndexLifecycleResult $result): SearchIndexLifecycleRegistrySyncResult
    {
        return SearchIndexLifecycleRegistrySyncResult::skipped('Search index registry synchronization is disabled.');
    }
}
