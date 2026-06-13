<?php

declare(strict_types=1);

namespace App\Searching\Service\Indexing;

use App\Searching\ServiceInterface\Indexing\SearchDocumentIndexerInterface;
use App\Searching\ServiceInterface\Indexing\SearchIncrementalIndexerInterface;
use App\Searching\ServiceInterface\Indexing\SearchIndexedResourceTrackerInterface;
use App\Searching\ServiceInterface\Provider\SearchProviderInterface;
use App\Searching\Value\Document\SearchDocument;
use App\Searching\Value\Indexing\SearchDocumentChangeResult;

final readonly class SearchIncrementalIndexer implements SearchIncrementalIndexerInterface
{
    public function __construct(
        private SearchDocumentIndexerInterface $documentIndexer,
        private SearchProviderInterface $searchProvider,
        private SearchDocumentFingerprintCalculator $fingerprintCalculator = new SearchDocumentFingerprintCalculator(),
        private SearchIndexedResourceTrackerInterface $indexedResourceTracker = new NullSearchIndexedResourceTracker(),
    ) {
    }

    public function indexChangedDocument(SearchDocument $document, string $changeReason = 'changed'): SearchDocumentChangeResult
    {
        $fingerprint = $this->fingerprintCalculator->fingerprint($document);
        $wasCurrent = $this->indexedResourceTracker->isCurrent($fingerprint);

        $this->documentIndexer->index($document);

        return SearchDocumentChangeResult::indexed(
            component: $document->component,
            resourceType: $document->resourceType,
            resourceId: $document->resourceId,
            metadata: [
                'reason' => $changeReason,
                'document_hash' => $fingerprint->documentHash,
                'source_updated_at' => $fingerprint->sourceUpdatedAt->format(\DateTimeInterface::ATOM),
                'unchanged' => $wasCurrent,
            ],
        );
    }

    public function removeResource(string $component, string $resourceType, string $resourceId, string $changeReason = 'removed'): SearchDocumentChangeResult
    {
        $this->searchProvider->delete($component, $resourceType, $resourceId);
        $state = $this->indexedResourceTracker->markRemoved($component, $resourceType, $resourceId);

        return SearchDocumentChangeResult::removed(
            component: $component,
            resourceType: $resourceType,
            resourceId: $resourceId,
            metadata: [
                'reason' => $changeReason,
                'indexed_resource_status' => $state->status,
            ],
        );
    }
}
