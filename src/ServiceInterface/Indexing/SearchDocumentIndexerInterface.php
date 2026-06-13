<?php

declare(strict_types=1);

namespace App\Searching\ServiceInterface\Indexing;

use App\Searching\Value\Document\SearchDocument;

interface SearchDocumentIndexerInterface
{
    public function index(SearchDocument $document): void;

    /**
     * @param iterable<SearchDocument> $documents
     */
    public function bulkIndex(iterable $documents): void;
}
