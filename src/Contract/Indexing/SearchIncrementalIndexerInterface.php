<?php

declare(strict_types=1);

namespace App\Searching\Contract\Indexing;

use App\Searching\Value\Document\SearchDocument;
use App\Searching\Value\Indexing\SearchDocumentChangeResult;

interface SearchIncrementalIndexerInterface
{
    public function indexChangedDocument(SearchDocument $document, string $changeReason = 'changed'): SearchDocumentChangeResult;

    public function removeResource(string $component, string $resourceType, string $resourceId, string $changeReason = 'removed'): SearchDocumentChangeResult;
}
