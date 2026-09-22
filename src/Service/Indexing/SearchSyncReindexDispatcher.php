<?php

declare(strict_types=1);

namespace App\Searching\Service\Indexing;

use App\Searching\Builder\Indexing\SearchReindexIdempotencyKeyBuilder;
use App\Searching\Contract\Flow\SearchOperationLimiterInterface;
use App\Searching\Contract\Indexing\SearchReindexCoordinatorInterface;
use App\Searching\Contract\Indexing\SearchReindexDispatcherInterface;
use App\Searching\ValueObject\Flow\SearchOperationLimitRequest;
use App\Searching\ValueObject\Indexing\SearchReindexDispatchResult;
use App\Searching\ValueObject\Observability\SearchExecutionContext;

final readonly class SearchSyncReindexDispatcher implements SearchReindexDispatcherInterface
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
