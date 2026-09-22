<?php

declare(strict_types=1);

namespace App\Searching\Repository;

use App\Searching\Contract\Indexing\SearchReindexJobTrackerInterface;
use App\Searching\Entity\SearchReindexJobEntity;
use App\Searching\ValueObject\Observability\SearchExecutionContext;
use Doctrine\ORM\EntityManagerInterface;

final readonly class SearchDoctrineReindexJobTracker implements SearchReindexJobTrackerInterface
{
    public function __construct(
        private SearchReindexJobRepository $repository,
        private EntityManagerInterface $entityManager,
        private bool $flushImmediately = true,
    ) {
    }

    public function request(?string $component = null, ?string $resourceType = null, ?string $requestedBy = null, ?\DateTimeImmutable $changedSince = null, ?string $idempotencyKey = null, ?string $dispatchMode = null, ?SearchExecutionContext $executionContext = null): SearchReindexJobEntity
    {
        $job = new SearchReindexJobEntity(bin2hex(random_bytes(16)), $component, $resourceType, $requestedBy, $changedSince, $idempotencyKey, $dispatchMode, $executionContext);
        $this->entityManager->persist($job);
        $this->flushIfNeeded();

        return $job;
    }

    public function markQueued(string $jobKey, string $dispatchMode, string $idempotencyKey): void
    {
        $job = $this->repository->findOneByJobKey($jobKey);
        if (null === $job) {
            return;
        }

        $job->markQueued($dispatchMode, $idempotencyKey);
        $this->entityManager->persist($job);
        $this->flushIfNeeded();
    }

    public function markDispatchFailed(string $jobKey, string $errorMessage): void
    {
        $job = $this->repository->findOneByJobKey($jobKey);
        if (null === $job) {
            return;
        }

        $job->markDispatchFailed($errorMessage);
        $this->entityManager->persist($job);
        $this->flushIfNeeded();
    }

    public function markRunning(string $jobKey, int $providerCount): void
    {
        $job = $this->repository->findOneByJobKey($jobKey);
        if (null === $job) {
            return;
        }

        $job->markRunning($providerCount);
        $this->entityManager->persist($job);
        $this->flushIfNeeded();
    }

    public function markCompleted(string $jobKey, int $providerCount, int $processedCount, int $failedCount, array $errors = []): void
    {
        $job = $this->repository->findOneByJobKey($jobKey);
        if (null === $job) {
            return;
        }

        $job->markCompleted($providerCount, $processedCount, $failedCount, $errors);
        $this->entityManager->persist($job);
        $this->flushIfNeeded();
    }

    public function markFailed(string $jobKey, int $providerCount, int $processedCount, int $failedCount, array $errors): void
    {
        $job = $this->repository->findOneByJobKey($jobKey);
        if (null === $job) {
            return;
        }

        $job->markFailed($providerCount, $processedCount, $failedCount, $errors);
        $this->entityManager->persist($job);
        $this->flushIfNeeded();
    }

    private function flushIfNeeded(): void
    {
        if ($this->flushImmediately) {
            $this->entityManager->flush();
        }
    }
}
