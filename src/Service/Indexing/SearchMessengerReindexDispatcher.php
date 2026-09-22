<?php

declare(strict_types=1);

namespace App\Searching\Service\Indexing;

use App\Searching\Builder\Indexing\SearchReindexIdempotencyKeyBuilder;
use App\Searching\Contract\Flow\SearchOperationLimiterInterface;
use App\Searching\Contract\Indexing\SearchReindexDispatcherInterface;
use App\Searching\Contract\Indexing\SearchReindexDuplicateGuardInterface;
use App\Searching\Contract\Indexing\SearchReindexJobTrackerInterface;
use App\Searching\Message\SearchReindexMessage;
use App\Searching\ValueObject\Flow\SearchOperationLimitRequest;
use App\Searching\ValueObject\Indexing\SearchReindexDispatchResult;
use App\Searching\ValueObject\Observability\SearchExecutionContext;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Defines the search messenger reindex dispatcher responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchMessengerReindexDispatcher implements SearchReindexDispatcherInterface
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private SearchReindexJobTrackerInterface $jobTracker,
        private SearchReindexIdempotencyKeyBuilder $idempotencyKeyBuilder,
        private SearchReindexDuplicateGuardInterface $duplicateGuard,
        private SearchOperationLimiterInterface $operationLimiter,
        private int $maxAttempts = 3,
    ) {
    }

    /**
     * Dispatches the dispatch through the Searching component asynchronous boundary.
     */
    public function dispatch(
        ?string $component = null,
        ?string $resourceType = null,
        ?\DateTimeImmutable $changedSince = null,
        ?string $requestedBy = null,
        ?SearchExecutionContext $executionContext = null,
    ): SearchReindexDispatchResult {
        $limitDecision = $this->operationLimiter->decide(SearchOperationLimitRequest::forReindexDispatch($component, $resourceType, $changedSince, $requestedBy));

        if (!$limitDecision->allowed) {
            return new SearchReindexDispatchResult(
                jobId: 'limited',
                mode: 'messenger',
                queued: false,
                metadata: [
                    'limited' => true,
                    'operation_limit' => $limitDecision->toMetadata(),
                    'requested_by' => $requestedBy,
                    'changed_since' => $changedSince?->format(\DateTimeInterface::ATOM),
                    'execution_context' => $executionContext?->toMetadata() ?? [],
                ],
            );
        }

        $idempotencyKey = $this->idempotencyKeyBuilder->build($component, $resourceType, $changedSince);
        $duplicate = $this->duplicateGuard->findOpenDuplicate($idempotencyKey);

        if (null !== $duplicate) {
            return new SearchReindexDispatchResult(
                jobId: $duplicate->getJobKey(),
                mode: 'messenger',
                queued: false,
                metadata: [
                    'duplicate' => true,
                    'idempotency_key' => $idempotencyKey,
                    'duplicate_status' => $duplicate->getStatus(),
                    'requested_by' => $requestedBy,
                    'changed_since' => $changedSince?->format(\DateTimeInterface::ATOM),
                    'operation_limit' => $limitDecision->toMetadata(),
                    'execution_context' => $executionContext?->toMetadata() ?? [],
                ],
            );
        }

        $job = $this->jobTracker->request($component, $resourceType, $requestedBy, $changedSince, $idempotencyKey, 'messenger', $executionContext);
        $jobId = $job->getJobKey();
        $this->jobTracker->markQueued($jobId, 'messenger', $idempotencyKey);

        $this->messageBus->dispatch(new SearchReindexMessage(
            jobKey: $jobId,
            component: $component,
            resourceType: $resourceType,
            changedSince: $changedSince?->format(\DateTimeInterface::ATOM),
            requestedBy: $requestedBy,
            idempotencyKey: $idempotencyKey,
            attempt: 1,
            maxAttempts: $this->maxAttempts,
            executionContext: $executionContext,
        ));

        return new SearchReindexDispatchResult(
            jobId: $jobId,
            mode: 'messenger',
            queued: true,
            metadata: [
                'idempotency_key' => $idempotencyKey,
                'max_attempts' => $this->maxAttempts,
                'requested_by' => $requestedBy,
                'changed_since' => $changedSince?->format(\DateTimeInterface::ATOM),
                'execution_context' => $executionContext?->toMetadata() ?? [],
            ],
        );
    }
}
