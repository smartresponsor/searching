<?php

declare(strict_types=1);

namespace App\Searching\Service\Health;

use App\Searching\Contract\Health\SearchHealthCheckerInterface;
use App\Searching\Contract\Indexing\SearchIndexedResourceReaderInterface;
use App\Searching\Contract\Indexing\SearchIndexReaderInterface;
use App\Searching\Contract\Indexing\SearchReindexJobReaderInterface;
use App\Searching\Contract\Registry\SearchableResourceRegistryInterface;
use App\Searching\Service\Provider\SearchProviderStatusCollector;
use App\Searching\ValueObject\Health\SearchHealthIndicator;
use App\Searching\ValueObject\Health\SearchHealthReport;
use App\Searching\ValueObject\Indexing\SearchIndexCriteria;
use App\Searching\ValueObject\Indexing\SearchIndexedResourceCriteria;
use App\Searching\ValueObject\Indexing\SearchReindexJobCriteria;

final readonly class SearchHealthChecker implements SearchHealthCheckerInterface
{
    public function __construct(
        private SearchProviderStatusCollector $providerStatusCollector,
        private SearchableResourceRegistryInterface $resourceRegistry,
        private SearchIndexReaderInterface $indexReader,
        private SearchIndexedResourceReaderInterface $indexedResourceReader,
        private SearchReindexJobReaderInterface $reindexJobReader,
        private int $backlogWarningThreshold = 10,
        private int $staleWarningThreshold = 100,
    ) {
    }

    public function check(): SearchHealthReport
    {
        $indicators = [
            $this->checkProviders(),
            $this->checkRegistry(),
            $this->checkLifecycleReadiness(),
            $this->checkIndexedResourceFreshness(),
            $this->checkReindexBacklog(),
        ];

        return new SearchHealthReport(
            status: $this->overallStatus($indicators),
            indicators: $indicators,
            checkedAt: new \DateTimeImmutable(),
            metadata: [
                'component' => 'searching',
                'readiness_model' => 'provider_registry_lifecycle_freshness_backlog',
            ],
        );
    }

    private function checkProviders(): SearchHealthIndicator
    {
        $statuses = $this->providerStatusCollector->collect();
        $available = 0;
        $providerMetadata = [];

        foreach ($statuses as $nameEntity => $status) {
            if ($status->available) {
                ++$available;
            }
            $providerMetadata[$nameEntity] = [
                'available' => $status->available,
                'status' => $status->status,
                'metadata' => $status->metadata,
            ];
        }

        $total = count($statuses);
        $status = 0 === $total ? 'unhealthy' : ($available > 0 ? 'healthy' : 'degraded');
        $summary = 0 === $total
            ? 'No search providers are registered.'
            : sprintf('%d of %d registered search providers are available.', $available, $total);

        return new SearchHealthIndicator(
            nameEntity: 'providers',
            status: $status,
            summary: $summary,
            metrics: ['total' => $total, 'available' => $available],
            metadata: ['providers' => $providerMetadata],
        );
    }

    private function checkRegistry(): SearchHealthIndicator
    {
        $resourceDefinitions = $this->resourceRegistry->definitions();
        $registeredIndexes = $this->indexReader->count(new SearchIndexCriteria(limit: 1));
        $enabledIndexes = $this->indexReader->count(new SearchIndexCriteria(enabled: true, limit: 1));

        $resourceCount = count($resourceDefinitions);
        $status = 'healthy';
        $summary = sprintf('%d searchable resources and %d enabled indexes are registered.', $resourceCount, $enabledIndexes);

        if ($resourceCount > 0 && 0 === $enabledIndexes) {
            $status = 'degraded';
            $summary = 'Searchable resources exist, but no enabled search indexes are registered.';
        }

        return new SearchHealthIndicator(
            nameEntity: 'registry',
            status: $status,
            summary: $summary,
            metrics: [
                'searchable_resources' => $resourceCount,
                'registered_indexes' => $registeredIndexes,
                'enabled_indexes' => $enabledIndexes,
            ],
        );
    }

    private function checkLifecycleReadiness(): SearchHealthIndicator
    {
        $indexes = $this->indexReader->list(new SearchIndexCriteria(enabled: true, limit: 500));
        $withError = 0;
        $deleted = 0;
        $unknown = 0;

        foreach ($indexes as $index) {
            if (null !== $index->getLastLifecycleError()) {
                ++$withError;
            }
            if ('deleted' === $index->getLifecycleStatus()) {
                ++$deleted;
            }
            if (null === $index->getLastLifecycleAt()) {
                ++$unknown;
            }
        }

        $status = $withError > 0 ? 'degraded' : 'healthy';
        if (count($indexes) > 0 && $deleted === count($indexes)) {
            $status = 'unhealthy';
        }

        return new SearchHealthIndicator(
            nameEntity: 'lifecycle',
            status: $status,
            summary: sprintf('%d enabled index definitions checked; %d have lifecycle errors.', count($indexes), $withError),
            metrics: [
                'enabled_indexes_checked' => count($indexes),
                'lifecycle_errors' => $withError,
                'deleted_indexes' => $deleted,
                'unknown_lifecycle_state' => $unknown,
            ],
        );
    }

    private function checkIndexedResourceFreshness(): SearchHealthIndicator
    {
        $stale = $this->indexedResourceReader->count(new SearchIndexedResourceCriteria(stale: true, limit: 1));
        $failed = $this->indexedResourceReader->count(new SearchIndexedResourceCriteria(status: 'failed', limit: 1));
        $removed = $this->indexedResourceReader->count(new SearchIndexedResourceCriteria(status: 'removed', limit: 1));

        $status = 'healthy';
        if ($failed > 0 || $stale >= $this->staleWarningThreshold) {
            $status = 'degraded';
        }

        return new SearchHealthIndicator(
            nameEntity: 'indexed_resources',
            status: $status,
            summary: sprintf('%d stale and %d failed indexed resources are recorded.', $stale, $failed),
            metrics: [
                'stale' => $stale,
                'failed' => $failed,
                'removed' => $removed,
                'stale_warning_threshold' => $this->staleWarningThreshold,
            ],
        );
    }

    private function checkReindexBacklog(): SearchHealthIndicator
    {
        $queued = $this->reindexJobReader->count(new SearchReindexJobCriteria(status: 'queued', limit: 1));
        $requested = $this->reindexJobReader->count(new SearchReindexJobCriteria(status: 'requested', limit: 1));
        $running = $this->reindexJobReader->count(new SearchReindexJobCriteria(status: 'running', limit: 1));
        $failed = $this->reindexJobReader->count(new SearchReindexJobCriteria(status: 'failed', limit: 1));
        $backlog = $queued + $requested + $running;

        $status = 'healthy';
        if ($failed > 0 || $backlog >= $this->backlogWarningThreshold) {
            $status = 'degraded';
        }

        return new SearchHealthIndicator(
            nameEntity: 'reindex_backlog',
            status: $status,
            summary: sprintf('%d active reindex jobs and %d failed jobs are recorded.', $backlog, $failed),
            metrics: [
                'queued' => $queued,
                'requested' => $requested,
                'running' => $running,
                'active_backlog' => $backlog,
                'failed' => $failed,
                'backlog_warning_threshold' => $this->backlogWarningThreshold,
            ],
        );
    }

    /**
     * @param list<SearchHealthIndicator> $indicators
     */
    private function overallStatus(array $indicators): string
    {
        $hasDegraded = false;

        foreach ($indicators as $indicator) {
            if ($indicator->isUnhealthy()) {
                return 'unhealthy';
            }
            if (!$indicator->isHealthy()) {
                $hasDegraded = true;
            }
        }

        return $hasDegraded ? 'degraded' : 'healthy';
    }
}
