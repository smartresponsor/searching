<?php

declare(strict_types=1);

namespace App\Searching\Service\Provider;

use App\Searching\ServiceInterface\Provider\SearchBulkOperationBuilderInterface;
use App\Searching\Value\Document\SearchDocument;
use App\Searching\Value\Provider\SearchBulkOperation;
use App\Searching\Value\Provider\SearchBulkOperationSet;

final class SearchBulkOperationBuilder implements SearchBulkOperationBuilderInterface
{
    public function __construct(
        private readonly SearchIndexNameBuilder $indexNameBuilder,
        private readonly SearchDocumentPayloadMapper $documentPayloadMapper,
    ) {
    }

    public function buildIndexOperation(SearchDocument $document, string $indexPrefix): SearchBulkOperation
    {
        return new SearchBulkOperation(
            operation: 'index',
            indexName: $this->indexNameBuilder->buildForDocument($document, $indexPrefix),
            documentId: $this->indexNameBuilder->buildDocumentId($document),
            payload: $this->documentPayloadMapper->map($document),
        );
    }

    public function buildIndexOperations(iterable $documents, string $indexPrefix): SearchBulkOperationSet
    {
        $operations = [];

        foreach ($documents as $document) {
            if (!$document instanceof SearchDocument) {
                throw new \InvalidArgumentException('Search bulk operation builder accepts only SearchDocument values.');
            }

            $operations[] = $this->buildIndexOperation($document, $indexPrefix);
        }

        return new SearchBulkOperationSet($operations);
    }
}
