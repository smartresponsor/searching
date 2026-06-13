<?php

declare(strict_types=1);

namespace App\Searching\ServiceInterface\Indexing;

use App\Searching\Value\Indexing\SearchIndexLifecycleRegistrySyncResult;
use App\Searching\Value\Provider\SearchIndexLifecycleResult;

interface SearchIndexLifecycleRegistrySynchronizerInterface
{
    public function sync(string $component, string $resourceType, SearchIndexLifecycleResult $result): SearchIndexLifecycleRegistrySyncResult;
}
