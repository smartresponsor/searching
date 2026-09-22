<?php

declare(strict_types=1);

namespace App\Searching\Contract\Provider;

use App\Searching\ValueObject\Document\SearchDocument;
use App\Searching\ValueObject\Provider\SearchBulkOperation;
use App\Searching\ValueObject\Provider\SearchBulkOperationSet;

/**
 * Defines the search bulk operation builder interface responsibility within the Searching component runtime and its typed boundaries.
 */
interface SearchBulkOperationBuilderInterface
{
    /**
     * Builds the index operation used by the Searching component execution and integration boundaries.
     */
    public function buildIndexOperation(SearchDocument $document, string $indexPrefix): SearchBulkOperation;

    /**
     * @param iterable<SearchDocument> $documents
     */
    public function buildIndexOperations(iterable $documents, string $indexPrefix): SearchBulkOperationSet;
}
