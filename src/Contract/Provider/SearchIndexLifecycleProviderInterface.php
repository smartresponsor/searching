<?php

declare(strict_types=1);

namespace App\Searching\Contract\Provider;

use App\Searching\ValueObject\Provider\SearchIndexLifecycleResult;

/**
 * Defines the search index lifecycle provider interface responsibility within the Searching component runtime and its typed boundaries.
 */
interface SearchIndexLifecycleProviderInterface
{
    /**
     * Indexes the exists through the Searching component indexing boundary.
     */
    public function indexExists(string $component, string $resourceType): bool;

    /**
     * Executes the ensure index responsibility defined by the Searching component contract.
     */
    public function ensureIndex(string $component, string $resourceType): SearchIndexLifecycleResult;

    /**
     * Deletes the index through the Searching component mutation boundary.
     */
    public function deleteIndex(string $component, string $resourceType): SearchIndexLifecycleResult;
}
