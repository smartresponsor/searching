<?php

declare(strict_types=1);

namespace App\Searching\Contract\Indexing;

use App\Searching\ValueObject\Indexing\SearchReindexResult;
use App\Searching\ValueObject\Observability\SearchExecutionContext;

interface SearchReindexCoordinatorInterface
{
    public function requestReindex(?string $component = null, ?string $resourceType = null, ?string $requestedBy = null, ?\DateTimeImmutable $changedSince = null): string;

    public function reindex(?string $component = null, ?string $resourceType = null, ?\DateTimeImmutable $changedSince = null, ?SearchExecutionContext $executionContext = null): SearchReindexResult;

    public function reindexExistingJob(string $jobId, ?string $component = null, ?string $resourceType = null, ?\DateTimeImmutable $changedSince = null, ?SearchExecutionContext $executionContext = null): SearchReindexResult;
}
