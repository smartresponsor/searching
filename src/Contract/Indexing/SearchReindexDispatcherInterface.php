<?php

declare(strict_types=1);

namespace App\Searching\Contract\Indexing;

use App\Searching\ValueObject\Indexing\SearchReindexDispatchResult;
use App\Searching\ValueObject\Observability\SearchExecutionContext;

/**
 * Defines the search reindex dispatcher interface responsibility within the Searching component runtime and its typed boundaries.
 */
interface SearchReindexDispatcherInterface
{
    /**
     * Dispatches the dispatch through the Searching component asynchronous boundary.
     */
    public function dispatch(
        ?string $component = null,
        ?string $resourceType = null,
        ?\DateTimeImmutable $changedSince = null,
        ?string $requestedBy = null,
        ?SearchExecutionContext $executionContext = null,
    ): SearchReindexDispatchResult;
}
