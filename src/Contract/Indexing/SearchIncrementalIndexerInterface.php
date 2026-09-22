<?php

declare(strict_types=1);

namespace App\Searching\Contract\Indexing;

use App\Searching\ValueObject\Document\SearchDocument;
use App\Searching\ValueObject\Indexing\SearchDocumentChangeResult;

interface SearchIncrementalIndexerInterface
{
    public function indexChangedDocument(SearchDocument $document, string $changeReason = 'changed'): SearchDocumentChangeResult;

    public function removeResource(string $component, string $resourceType, string $resourceId, string $changeReason = 'removed'): SearchDocumentChangeResult;
}
