<?php

declare(strict_types=1);

namespace App\Searching\Contract\Indexing;

use App\Searching\ValueObject\Indexing\SearchDocumentFingerprint;
use App\Searching\ValueObject\Indexing\SearchIndexedResourceState;

interface SearchIndexedResourceTrackerInterface
{
    public function find(string $component, string $resourceType, string $resourceId): ?SearchIndexedResourceState;

    public function isCurrent(SearchDocumentFingerprint $fingerprint): bool;

    public function markIndexed(SearchDocumentFingerprint $fingerprint): SearchIndexedResourceState;

    public function markUnchanged(SearchDocumentFingerprint $fingerprint): SearchIndexedResourceState;

    public function markFailed(SearchDocumentFingerprint $fingerprint, string $errorMessage): SearchIndexedResourceState;

    public function markRemoved(string $component, string $resourceType, string $resourceId): SearchIndexedResourceState;
}
