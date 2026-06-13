<?php

declare(strict_types=1);

namespace App\Searching\Service\Indexing;

use App\Searching\ServiceInterface\Flow\SearchOperationLimiterInterface;
use App\Searching\ServiceInterface\Indexing\SearchReindexCoordinatorInterface;
use App\Searching\ServiceInterface\Indexing\SearchReindexDispatcherInterface;
use App\Searching\Value\Flow\SearchOperationLimitRequest;
use App\Searching\Value\Indexing\SearchReindexDispatchResult;
use App\Searching\Value\Observability\SearchExecutionContext;

final readonly class SyncSearchReindexDispatcher implements SearchReindexDispatcherInterface
{
    public function __construct(
        private SearchReindexCoordinatorInterface $coordinator,
        private SearchOperationLimiterInterface $operationLimiter,
        private SearchReindexIdempotencyKeyBuilder $idempotencyKeyBuilder = new SearchReindexIdempotencyKeyBuilder(),
    ) {
    }

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
                mode: 'sync',
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

        $result = $this->coordinator->reindex($component, $resourceType, $changedSince, $executionContext);

        return new SearchReindexDispatchResult(
            jobId: $result->jobId,
            mode: 'sync',
            queued: false,
            syncResult: $result,
            metadata: [
                'idempotency_key' => $this->idempotencyKeyBuilder->build($component, $resourceType, $changedSince),
                'requested_by' => $requestedBy,
                'changed_since' => $changedSince?->format(\DateTimeInterface::ATOM),
                'operation_limit' => $limitDecision->toMetadata(),
                'execution_context' => $executionContext?->toMetadata() ?? [],
            ],
        );
    }
}
