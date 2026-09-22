<?php

declare(strict_types=1);

namespace App\Searching\Service\Serialization;

use App\Searching\Entity\SearchReindexJobEntity;

/**
 * Defines the search reindex job serializer responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchReindexJobSerializer
{
    /**
     * @return array<string, mixed>
     */
    public function serialize(SearchReindexJobEntity $job): array
    {
        return [
            'id' => $job->getId(),
            'jobKey' => $job->getJobKey(),
            'jobId' => $job->getJobKey(),
            'component' => $job->getComponent(),
            'resourceType' => $job->getResourceType(),
            'status' => $job->getStatus(),
            'requestedBy' => $job->getRequestedBy(),
            'changedSince' => $job->getChangedSince()?->format(DATE_ATOM),
            'idempotencyKey' => $job->getIdempotencyKey(),
            'dispatchMode' => $job->getDispatchMode(),
            'correlationId' => $job->getCorrelationId(),
            'requestId' => $job->getRequestId(),
            'sourceComponent' => $job->getSourceComponent(),
            'sourceOperation' => $job->getSourceOperation(),
            'executionContext' => $job->getExecutionContext(),
            'queuedAt' => $job->getQueuedAt()?->format(DATE_ATOM),
            'messageAttempts' => $job->getMessageAttempts(),
            'lastMessageFailureAt' => $job->getLastMessageFailureAt()?->format(DATE_ATOM),
            'startedAt' => $job->getStartedAt()?->format(DATE_ATOM),
            'finishedAt' => $job->getFinishedAt()?->format(DATE_ATOM),
            'providerCount' => $job->getProviderCount(),
            'processedCount' => $job->getProcessedCount(),
            'documentCount' => $job->getProcessedCount(),
            'failedCount' => $job->getFailedCount(),
            'errors' => $job->getErrors(),
            'errorMessage' => $job->getErrorMessage(),
            'createdAt' => $job->getCreatedAt()->format(DATE_ATOM),
            'updatedAt' => $job->getUpdatedAt()->format(DATE_ATOM),
        ];
    }

    /**
     * @param iterable<SearchReindexJobEntity> $jobs
     *
     * @return list<array<string, mixed>>
     */
    public function serializeList(iterable $jobs): array
    {
        $payload = [];
        foreach ($jobs as $job) {
            $payload[] = $this->serialize($job);
        }

        return $payload;
    }
}
