<?php

declare(strict_types=1);

namespace App\Searching\Contract\Indexing;

use App\Searching\Entity\SearchReindexJobEntity;
use App\Searching\Value\Observability\SearchExecutionContext;

interface SearchReindexJobTrackerInterface
{
    public function request(?string $component = null, ?string $resourceType = null, ?string $requestedBy = null, ?\DateTimeImmutable $changedSince = null, ?string $idempotencyKey = null, ?string $dispatchMode = null, ?SearchExecutionContext $executionContext = null): SearchReindexJobEntity;

    public function markQueued(string $jobKey, string $dispatchMode, string $idempotencyKey): void;

    public function markDispatchFailed(string $jobKey, string $errorMessage): void;

    public function markRunning(string $jobKey, int $providerCount): void;

    /**
     * @param list<string> $errors
     */
    public function markCompleted(string $jobKey, int $providerCount, int $processedCount, int $failedCount, array $errors = []): void;

    /**
     * @param list<string> $errors
     */
    public function markFailed(string $jobKey, int $providerCount, int $processedCount, int $failedCount, array $errors): void;
}
