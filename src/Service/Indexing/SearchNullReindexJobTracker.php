<?php

declare(strict_types=1);

namespace App\Searching\Service\Indexing;

use App\Searching\Contract\Indexing\SearchReindexJobTrackerInterface;
use App\Searching\Entity\SearchReindexJobEntity;
use App\Searching\ValueObject\Observability\SearchExecutionContext;

/**
 * Defines the search null reindex job tracker responsibility within the Searching component runtime and its typed boundaries.
 */
final class SearchNullReindexJobTracker implements SearchReindexJobTrackerInterface
{
    /**
     * Executes the request responsibility defined by the Searching component contract.
     */
    public function request(?string $component = null, ?string $resourceType = null, ?string $requestedBy = null, ?\DateTimeImmutable $changedSince = null, ?string $idempotencyKey = null, ?string $dispatchMode = null, ?SearchExecutionContext $executionContext = null): SearchReindexJobEntity
    {
        return new SearchReindexJobEntity(bin2hex(random_bytes(16)), $component, $resourceType, $requestedBy, $changedSince, $idempotencyKey, $dispatchMode, $executionContext);
    }

    /**
     * Executes the mark queued responsibility defined by the Searching component contract.
     */
    public function markQueued(string $jobKey, string $dispatchMode, string $idempotencyKey): void
    {
        unset($jobKey, $dispatchMode, $idempotencyKey);
    }

    /**
     * Executes the mark dispatch failed responsibility defined by the Searching component contract.
     */
    public function markDispatchFailed(string $jobKey, string $errorMessage): void
    {
        unset($jobKey, $errorMessage);
    }

    /**
     * Executes the mark running responsibility defined by the Searching component contract.
     */
    public function markRunning(string $jobKey, int $providerCount): void
    {
        unset($jobKey, $providerCount);
    }

    /**
     * Executes the mark completed responsibility defined by the Searching component contract.
     */
    public function markCompleted(string $jobKey, int $providerCount, int $processedCount, int $failedCount, array $errors = []): void
    {
        unset($jobKey, $providerCount, $processedCount, $failedCount, $errors);
    }

    /**
     * Executes the mark failed responsibility defined by the Searching component contract.
     */
    public function markFailed(string $jobKey, int $providerCount, int $processedCount, int $failedCount, array $errors): void
    {
        unset($jobKey, $providerCount, $processedCount, $failedCount, $errors);
    }
}
