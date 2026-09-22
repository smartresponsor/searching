<?php

declare(strict_types=1);

namespace App\Searching\Contract\Indexing;

use App\Searching\ValueObject\Indexing\SearchIndexLifecycleRegistrySyncResult;
use App\Searching\ValueObject\Provider\SearchIndexLifecycleResult;

/**
 * Defines the search index lifecycle registry synchronizer interface responsibility within the Searching component runtime and its typed boundaries.
 */
interface SearchIndexLifecycleRegistrySynchronizerInterface
{
    /**
     * Executes the sync responsibility defined by the Searching component contract.
     */
    public function sync(string $component, string $resourceType, SearchIndexLifecycleResult $result): SearchIndexLifecycleRegistrySyncResult;
}
