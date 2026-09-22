<?php

declare(strict_types=1);

namespace App\Searching\Contract\Indexing;

use App\Searching\ValueObject\Document\SearchDocument;
use App\Searching\ValueObject\Indexing\SearchDocumentChangeResult;

/**
 * Defines the search incremental indexer interface responsibility within the Searching component runtime and its typed boundaries.
 */
interface SearchIncrementalIndexerInterface
{
    /**
     * Indexes the changed document through the Searching component indexing boundary.
     */
    public function indexChangedDocument(SearchDocument $document, string $changeReason = 'changed'): SearchDocumentChangeResult;

    /**
     * Removes the resource through the Searching component mutation boundary.
     */
    public function removeResource(string $component, string $resourceType, string $resourceId, string $changeReason = 'removed'): SearchDocumentChangeResult;
}
