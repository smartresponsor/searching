<?php

declare(strict_types=1);

namespace App\Searching\Contract\Provider;

use App\Searching\Value\Document\SearchDocument;
use App\Searching\Value\Provider\SearchBulkOperation;
use App\Searching\Value\Provider\SearchBulkOperationSet;

interface SearchBulkOperationBuilderInterface
{
    public function buildIndexOperation(SearchDocument $document, string $indexPrefix): SearchBulkOperation;

    /**
     * @param iterable<SearchDocument> $documents
     */
    public function buildIndexOperations(iterable $documents, string $indexPrefix): SearchBulkOperationSet;
}
