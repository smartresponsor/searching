<?php

declare(strict_types=1);

namespace App\Searching\Contract\Indexing;

use App\Searching\Entity\SearchReindexJobEntity;
use App\Searching\ValueObject\Observability\SearchExecutionContext;

/**
 * Defines the search reindex job tracker interface responsibility within the Searching component runtime and its typed boundaries.
 */
interface SearchReindexJobTrackerInterface
{
    /**
     * Executes the request responsibility defined by the Searching component contract.
     */
    public function request(?string $component = null, ?string $resourceType = null, ?string $requestedBy = null, ?\DateTimeImmutable $changedSince = null, ?string $idempotencyKey = null, ?string $dispatchMode = null, ?SearchExecutionContext $executionContext = null): SearchReindexJobEntity;

    /**
     * Executes the mark queued responsibility defined by the Searching component contract.
     */
    public function markQueued(string $jobKey, string $dispatchMode, string $idempotencyKey): void;

    /**
     * Executes the mark dispatch failed responsibility defined by the Searching component contract.
     */
    public function markDispatchFailed(string $jobKey, string $errorMessage): void;

    /**
     * Executes the mark running responsibility defined by the Searching component contract.
     */
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
