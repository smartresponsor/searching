<?php

declare(strict_types=1);

namespace App\Searching\Contract\Indexing;

use App\Searching\ValueObject\Document\SearchDocument;

/**
 * Defines the search document indexer interface responsibility within the Searching component runtime and its typed boundaries.
 */
interface SearchDocumentIndexerInterface
{
    /**
     * Indexes the index through the Searching component indexing boundary.
     */
    public function index(SearchDocument $document): void;

    /**
     * @param iterable<SearchDocument> $documents
     */
    public function bulkIndex(iterable $documents): void;
}
