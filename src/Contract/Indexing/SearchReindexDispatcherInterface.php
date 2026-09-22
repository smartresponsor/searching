<?php

declare(strict_types=1);

namespace App\Searching\Contract\Indexing;

use App\Searching\ValueObject\Indexing\SearchReindexDispatchResult;
use App\Searching\ValueObject\Observability\SearchExecutionContext;

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
