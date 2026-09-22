<?php

declare(strict_types=1);

namespace App\Searching\Contract\Indexing;

use App\Searching\ValueObject\Provider\SearchIndexLifecycleResult;

/**
 * Defines the search index lifecycle manager interface responsibility within the Searching component runtime and its typed boundaries.
 */
interface SearchIndexLifecycleManagerInterface
{
    /**
     * Executes the ensure responsibility defined by the Searching component contract.
     */
    public function ensure(string $providerName, string $component, string $resourceType): SearchIndexLifecycleResult;

    /**
     * Deletes the delete through the Searching component mutation boundary.
     */
    public function delete(string $providerName, string $component, string $resourceType): SearchIndexLifecycleResult;

    /**
     * @return array<string, SearchIndexLifecycleResult>
     */
    public function ensureForAllProviders(string $component, string $resourceType): array;
}
