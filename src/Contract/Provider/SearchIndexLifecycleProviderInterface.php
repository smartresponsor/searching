<?php

declare(strict_types=1);

namespace App\Searching\Contract\Provider;

use App\Searching\Value\Provider\SearchIndexLifecycleResult;

interface SearchIndexLifecycleProviderInterface
{
    public function indexExists(string $component, string $resourceType): bool;

    public function ensureIndex(string $component, string $resourceType): SearchIndexLifecycleResult;

    public function deleteIndex(string $component, string $resourceType): SearchIndexLifecycleResult;
}
