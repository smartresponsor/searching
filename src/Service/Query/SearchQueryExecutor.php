<?php

declare(strict_types=1);

namespace App\Searching\Service\Query;

use App\Searching\Contract\Flow\SearchOperationLimiterInterface;
use App\Searching\Contract\Provider\SearchProviderInterface;
use App\Searching\Contract\Query\SearchQueryExecutorInterface;
use App\Searching\Contract\Query\SearchQueryLoggerInterface;
use App\Searching\Contract\Query\SearchResultHydratorInterface;
use App\Searching\Contract\Security\SearchPermissionFilterInterface;
use App\Searching\Exception\SearchOperationLimitedException;
use App\Searching\ValueObject\Flow\SearchOperationLimitRequest;
use App\Searching\ValueObject\Query\SearchQuery;
use App\Searching\ValueObject\Query\SearchQueryExecutionTrace;
use App\Searching\ValueObject\Result\SearchResult;

/**
 * Defines the search query executor responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchQueryExecutor implements SearchQueryExecutorInterface
{
    public function __construct(
        private SearchProviderInterface $searchProvider,
        private SearchResultHydratorInterface $resultHydrator,
        private SearchPermissionFilterInterface $permissionFilter,
        private SearchQueryLoggerInterface $queryLogger,
        private SearchOperationLimiterInterface $operationLimiter,
        private bool $resultHydration = true,
        private bool $permissionFiltering = true,
    ) {
    }

    /**
     * Executes the execute operation through the Searching component runtime boundary.
     */
    public function execute(SearchQuery $query): SearchResult
    {
        $startedAt = microtime(true);
        $executedAt = new \DateTimeImmutable();
        $providerName = $this->searchProvider->getStatus()->nameEntity;
        $limitDecision = $this->operationLimiter->decide(SearchOperationLimitRequest::forSearchQuery($query));

        if (!$limitDecision->allowed) {
            $this->queryLogger->log(new SearchQueryExecutionTrace(
                query: $query->query,
                userId: $query->userId,
                vendorId: $query->vendorId,
                providerName: $providerName,
                providerTotal: 0,
                returnedTotal: 0,
                deniedCount: 0,
                durationMs: $this->durationMs($startedAt),
                executedAt: $executedAt,
                successful: false,
                errorClass: SearchOperationLimitedException::class,
                errorMessage: $limitDecision->reason,
                metadata: [
                    'components' => $query->components,
                    'resourceTypes' => $query->resourceTypes,
                    'page' => $query->page,
                    'limit' => $query->limit,
                    'locale' => $query->locale,
                    'operation_limit' => $limitDecision->toMetadata(),
                    'execution_context' => $this->contextMetadata($query),
                ],
                executionContext: $query->executionContext,
            ));

            throw new SearchOperationLimitedException(SearchOperationLimitRequest::forSearchQuery($query), $limitDecision);
        }

        try {
            $providerResult = $this->searchProvider->search($query);
            $items = $providerResult->items;
            $deniedCount = 0;
            $metadata = $providerResult->metadata + [
                'provider_name' => $providerName,
                'provider_total' => $providerResult->total,
                'permission_filtering' => $this->permissionFiltering,
                'operation_limit' => $limitDecision->toMetadata(),
                'execution_context' => $this->contextMetadata($query),
            ];

            if ($this->resultHydration) {
                $hydrationResult = $this->resultHydrator->hydrate($items);
                $items = $hydrationResult->items;
                $metadata['result_hydration'] = true;
                $metadata['hydration_original_count'] = $hydrationResult->originalCount;
                $metadata['hydration_hydrated_count'] = $hydrationResult->hydratedCount();
                $metadata['hydration_dropped_count'] = $hydrationResult->droppedCount();
                $metadata['hydration_dropped_items'] = $hydrationResult->droppedItems;
            } else {
                $metadata['result_hydration'] = false;
            }

            if ($this->permissionFiltering) {
                $filterResult = $this->permissionFilter->filterResult($items, $query);
                $items = $filterResult->allowedItems;
                $deniedCount = $filterResult->deniedCount();
                $metadata['permission_original_count'] = $filterResult->originalCount;
                $metadata['permission_allowed_count'] = $filterResult->allowedCount();
                $metadata['permission_denied_count'] = $deniedCount;
                $metadata['permission_denied_items'] = $filterResult->deniedItems;
            }

            $durationMs = $this->durationMs($startedAt);
            $trace = new SearchQueryExecutionTrace(
                query: $query->query,
                userId: $query->userId,
                vendorId: $query->vendorId,
                providerName: $providerName,
                providerTotal: $providerResult->total,
                returnedTotal: count($items),
                deniedCount: $deniedCount,
                durationMs: $durationMs,
                executedAt: $executedAt,
                providerMetadata: $providerResult->metadata,
                metadata: [
                    'components' => $query->components,
                    'resourceTypes' => $query->resourceTypes,
                    'page' => $query->page,
                    'limit' => $query->limit,
                    'locale' => $query->locale,
                    'result_hydration' => $this->resultHydration,
                    'hydration_dropped_count' => $metadata['hydration_dropped_count'] ?? 0,
                    'operation_limit' => $limitDecision->toMetadata(),
                    'execution_context' => $this->contextMetadata($query),
                ],
                executionContext: $query->executionContext,
            );
            $this->queryLogger->log($trace);

            $metadata['query_trace'] = [
                'provider_name' => $trace->providerName,
                'duration_ms' => $trace->durationMs,
                'returned_total' => $trace->returnedTotal,
                'denied_count' => $trace->deniedCount,
                'hydration_dropped_count' => $metadata['hydration_dropped_count'] ?? 0,
                'successful' => $trace->successful,
                'operation_limit_status' => $limitDecision->status(),
                'correlation_id' => $query->executionContext?->correlationId,
                'request_id' => $query->executionContext?->requestId,
            ];

            return new SearchResult(
                query: $query->query,
                total: count($items),
                page: $query->page,
                limit: $query->limit,
                items: $items,
                facets: $providerResult->facets,
                suggestions: $providerResult->suggestions,
                metadata: $metadata,
            );
        } catch (\Throwable $exception) {
            $this->queryLogger->log(new SearchQueryExecutionTrace(
                query: $query->query,
                userId: $query->userId,
                vendorId: $query->vendorId,
                providerName: $providerName,
                providerTotal: 0,
                returnedTotal: 0,
                deniedCount: 0,
                durationMs: $this->durationMs($startedAt),
                executedAt: $executedAt,
                successful: false,
                errorClass: $exception::class,
                errorMessage: $exception->getMessage(),
                metadata: [
                    'components' => $query->components,
                    'resourceTypes' => $query->resourceTypes,
                    'page' => $query->page,
                    'limit' => $query->limit,
                    'locale' => $query->locale,
                    'result_hydration' => $this->resultHydration,
                    'execution_context' => $this->contextMetadata($query),
                ],
                executionContext: $query->executionContext,
            ));

            throw $exception;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function contextMetadata(SearchQuery $query): array
    {
        return $query->executionContext?->toMetadata() ?? [];
    }

    private function durationMs(float $startedAt): float
    {
        return round((microtime(true) - $startedAt) * 1000, 3);
    }
}
