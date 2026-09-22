<?php

declare(strict_types=1);

namespace App\Searching\Contract\Indexing;

use App\Searching\ValueObject\Indexing\SearchDocumentFingerprint;
use App\Searching\ValueObject\Indexing\SearchIndexedResourceState;

/**
 * Defines the search indexed resource tracker interface responsibility within the Searching component runtime and its typed boundaries.
 */
interface SearchIndexedResourceTrackerInterface
{
    /**
     * Finds the find through the Searching component read or persistence boundary.
     */
    public function find(string $component, string $resourceType, string $resourceId): ?SearchIndexedResourceState;

    public function isCurrent(SearchDocumentFingerprint $fingerprint): bool;

    /**
     * Executes the mark indexed responsibility defined by the Searching component contract.
     */
    public function markIndexed(SearchDocumentFingerprint $fingerprint): SearchIndexedResourceState;

    /**
     * Executes the mark unchanged responsibility defined by the Searching component contract.
     */
    public function markUnchanged(SearchDocumentFingerprint $fingerprint): SearchIndexedResourceState;

    /**
     * Executes the mark failed responsibility defined by the Searching component contract.
     */
    public function markFailed(SearchDocumentFingerprint $fingerprint, string $errorMessage): SearchIndexedResourceState;

    /**
     * Executes the mark removed responsibility defined by the Searching component contract.
     */
    public function markRemoved(string $component, string $resourceType, string $resourceId): SearchIndexedResourceState;
}
