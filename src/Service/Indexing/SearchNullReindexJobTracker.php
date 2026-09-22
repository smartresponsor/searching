<?php

declare(strict_types=1);

namespace App\Searching\Service\Indexing;

use App\Searching\Contract\Indexing\SearchReindexJobTrackerInterface;
use App\Searching\Entity\SearchReindexJobEntity;
use App\Searching\ValueObject\Observability\SearchExecutionContext;

final class SearchNullReindexJobTracker implements SearchReindexJobTrackerInterface
{
    public function request(?string $component = null, ?string $resourceType = null, ?string $requestedBy = null, ?\DateTimeImmutable $changedSince = null, ?string $idempotencyKey = null, ?string $dispatchMode = null, ?SearchExecutionContext $executionContext = null): SearchReindexJobEntity
    {
        return new SearchReindexJobEntity(bin2hex(random_bytes(16)), $component, $resourceType, $requestedBy, $changedSince, $idempotencyKey, $dispatchMode, $executionContext);
    }

    public function markQueued(string $jobKey, string $dispatchMode, string $idempotencyKey): void
    {
        unset($jobKey, $dispatchMode, $idempotencyKey);
    }

    public function markDispatchFailed(string $jobKey, string $errorMessage): void
    {
        unset($jobKey, $errorMessage);
    }

    public function markRunning(string $jobKey, int $providerCount): void
    {
        unset($jobKey, $providerCount);
    }

    public function markCompleted(string $jobKey, int $providerCount, int $processedCount, int $failedCount, array $errors = []): void
    {
        unset($jobKey, $providerCount, $processedCount, $failedCount, $errors);
    }

    public function markFailed(string $jobKey, int $providerCount, int $processedCount, int $failedCount, array $errors): void
    {
        unset($jobKey, $providerCount, $processedCount, $failedCount, $errors);
    }
}
