<?php

declare(strict_types=1);

namespace App\Searching\Service\Indexing;

use App\Searching\Contract\Indexing\SearchDocumentIndexerInterface;
use App\Searching\Contract\Indexing\SearchReindexCoordinatorInterface;
use App\Searching\Contract\Indexing\SearchReindexJobTrackerInterface;
use App\Searching\Contract\Registry\SearchableResourceRegistryInterface;
use App\Searching\ValueObject\Indexing\SearchReindexResult;
use App\Searching\ValueObject\Observability\SearchExecutionContext;

/**
 * Defines the search reindex coordinator responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchReindexCoordinator implements SearchReindexCoordinatorInterface
{
    public function __construct(
        private SearchableResourceRegistryInterface $resourceRegistry,
        private SearchDocumentIndexerInterface $documentIndexer,
        private SearchReindexJobTrackerInterface $jobTracker = new SearchNullReindexJobTracker(),
    ) {
    }

    /**
     * Executes the request reindex responsibility defined by the Searching component contract.
     */
    public function requestReindex(?string $component = null, ?string $resourceType = null, ?string $requestedBy = null, ?\DateTimeImmutable $changedSince = null): string
    {
        return $this->jobTracker->request($component, $resourceType, $requestedBy, $changedSince)->getJobKey();
    }

    /**
     * Executes the reindex responsibility defined by the Searching component contract.
     */
    public function reindex(?string $component = null, ?string $resourceType = null, ?\DateTimeImmutable $changedSince = null, ?SearchExecutionContext $executionContext = null): SearchReindexResult
    {
        $job = $this->jobTracker->request($component, $resourceType, changedSince: $changedSince, executionContext: $executionContext);

        return $this->reindexExistingJob($job->getJobKey(), $component, $resourceType, $changedSince, $executionContext);
    }

    /**
     * Executes the reindex existing job responsibility defined by the Searching component contract.
     */
    public function reindexExistingJob(string $jobId, ?string $component = null, ?string $resourceType = null, ?\DateTimeImmutable $changedSince = null, ?SearchExecutionContext $executionContext = null): SearchReindexResult
    {
        $providers = $this->resourceRegistry->matching($component, $resourceType);
        $providerCount = count($providers);
        $documentCount = 0;
        $failedCount = 0;
        $errors = [];

        $this->jobTracker->markRunning($jobId, $providerCount);

        try {
            foreach ($providers as $provider) {
                try {
                    foreach ($provider->provideSearchDocuments($changedSince) as $document) {
                        $this->documentIndexer->index($document);
                        ++$documentCount;
                    }
                } catch (\Throwable $throwable) {
                    ++$failedCount;
                    $errors[] = sprintf(
                        '%s:%s failed: %s',
                        $provider->getSearchableComponentName(),
                        $provider->getSearchableResourceName(),
                        $throwable->getMessage(),
                    );
                }
            }
        } catch (\Throwable $throwable) {
            $errors[] = $throwable->getMessage();
            $this->jobTracker->markFailed($jobId, $providerCount, $documentCount, max(1, $failedCount), $errors);

            throw $throwable;
        }

        $this->jobTracker->markCompleted($jobId, $providerCount, $documentCount, $failedCount, $errors);

        return new SearchReindexResult($jobId, $providerCount, $documentCount, $failedCount, $errors, $executionContext);
    }
}
