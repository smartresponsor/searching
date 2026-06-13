<?php

declare(strict_types=1);

namespace App\Searching\ServiceInterface\Indexing;

use App\Searching\Value\Indexing\SearchReindexDispatchResult;
use App\Searching\Value\Observability\SearchExecutionContext;

interface SearchReindexDispatcherInterface
{
    public function dispatch(
        ?string $component = null,
        ?string $resourceType = null,
        ?\DateTimeImmutable $changedSince = null,
        ?string $requestedBy = null,
        ?SearchExecutionContext $executionContext = null,
    ): SearchReindexDispatchResult;
}
