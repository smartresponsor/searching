<?php

declare(strict_types=1);

namespace App\Searching\Service\Indexing;

use App\Searching\Contract\Indexing\SearchIndexedResourceTrackerInterface;
use App\Searching\ValueObject\Indexing\SearchDocumentFingerprint;
use App\Searching\ValueObject\Indexing\SearchIndexedResourceState;

/**
 * Defines the search null indexed resource tracker responsibility within the Searching component runtime and its typed boundaries.
 */
final class SearchNullIndexedResourceTracker implements SearchIndexedResourceTrackerInterface
{
    /**
     * Finds the find through the Searching component read or persistence boundary.
     */
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

    /**
     * Executes the mark indexed responsibility defined by the Searching component contract.
     */
    public function markIndexed(SearchDocumentFingerprint $fingerprint): SearchIndexedResourceState
    {
        return $this->state($fingerprint, 'indexed');
    }

    /**
     * Executes the mark unchanged responsibility defined by the Searching component contract.
     */
    public function markUnchanged(SearchDocumentFingerprint $fingerprint): SearchIndexedResourceState
    {
        return $this->state($fingerprint, 'unchanged');
    }

    /**
     * Executes the mark failed responsibility defined by the Searching component contract.
     */
    public function markFailed(SearchDocumentFingerprint $fingerprint, string $errorMessage): SearchIndexedResourceState
    {
        return $this->state($fingerprint, 'failed', $errorMessage);
    }

    /**
     * Executes the mark removed responsibility defined by the Searching component contract.
     */
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
