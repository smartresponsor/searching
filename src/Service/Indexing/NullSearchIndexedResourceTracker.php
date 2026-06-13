<?php

declare(strict_types=1);

namespace App\Searching\Service\Indexing;

use App\Searching\ServiceInterface\Indexing\SearchIndexedResourceTrackerInterface;
use App\Searching\Value\Indexing\SearchDocumentFingerprint;
use App\Searching\Value\Indexing\SearchIndexedResourceState;

final class NullSearchIndexedResourceTracker implements SearchIndexedResourceTrackerInterface
{
    public function find(string $component, string $resourceType, string $resourceId): ?SearchIndexedResourceState
    {
        unset($component, $resourceType, $resourceId);

        return null;
    }

    public function isCurrent(SearchDocumentFingerprint $fingerprint): bool
    {
        unset($fingerprint);

        return false;
    }

    public function markIndexed(SearchDocumentFingerprint $fingerprint): SearchIndexedResourceState
    {
        return $this->state($fingerprint, 'indexed');
    }

    public function markUnchanged(SearchDocumentFingerprint $fingerprint): SearchIndexedResourceState
    {
        return $this->state($fingerprint, 'unchanged');
    }

    public function markFailed(SearchDocumentFingerprint $fingerprint, string $errorMessage): SearchIndexedResourceState
    {
        return $this->state($fingerprint, 'failed', $errorMessage);
    }

    public function markRemoved(string $component, string $resourceType, string $resourceId): SearchIndexedResourceState
    {
        return new SearchIndexedResourceState(
            component: $component,
            resourceType: $resourceType,
            resourceId: $resourceId,
            documentHash: null,
            indexedAt: null,
            sourceUpdatedAt: null,
            status: 'removed',
        );
    }

    private function state(SearchDocumentFingerprint $fingerprint, string $status, ?string $errorMessage = null): SearchIndexedResourceState
    {
        return new SearchIndexedResourceState(
            component: $fingerprint->component,
            resourceType: $fingerprint->resourceType,
            resourceId: $fingerprint->resourceId,
            documentHash: $fingerprint->documentHash,
            indexedAt: 'indexed' === $status ? new \DateTimeImmutable() : null,
            sourceUpdatedAt: $fingerprint->sourceUpdatedAt,
            status: $status,
            errorMessage: $errorMessage,
        );
    }
}
