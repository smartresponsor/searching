<?php

declare(strict_types=1);

namespace App\Searching\ServiceInterface\Indexing;

use App\Searching\Value\Provider\SearchIndexLifecycleResult;

interface SearchIndexLifecycleManagerInterface
{
    public function ensure(string $providerName, string $component, string $resourceType): SearchIndexLifecycleResult;

    public function delete(string $providerName, string $component, string $resourceType): SearchIndexLifecycleResult;

    /**
     * @return array<string, SearchIndexLifecycleResult>
     */
    public function ensureForAllProviders(string $component, string $resourceType): array;
}
