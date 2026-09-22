<?php

declare(strict_types=1);

namespace App\Searching\Handler;

use App\Searching\Contract\Indexing\SearchReindexCoordinatorInterface;
use App\Searching\Contract\Indexing\SearchReindexJobTrackerInterface;
use App\Searching\Message\SearchReindexMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Defines the search reindex message handler responsibility within the Searching component runtime and its typed boundaries.
 */
#[AsMessageHandler]
final readonly class SearchReindexMessageHandler
{
    public function __construct(
        private SearchReindexCoordinatorInterface $coordinator,
        private SearchReindexJobTrackerInterface $jobTracker,
    ) {
    }

    public function __invoke(SearchReindexMessage $message): void
    {
        try {
            $this->coordinator->reindexExistingJob(
                jobId: $message->jobKey,
                component: $message->component,
                resourceType: $message->resourceType,
                changedSince: $message->getChangedSinceDate(),
                executionContext: $message->executionContext,
            );
        } catch (\Throwable $throwable) {
            $this->jobTracker->markDispatchFailed($message->jobKey, $throwable->getMessage());

            throw $throwable;
        }
    }
}
