<?php

declare(strict_types=1);

namespace App\Searching\Builder\Provider;

use App\Searching\Contract\Provider\SearchBulkOperationBuilderInterface;
use App\Searching\Service\Provider\SearchDocumentPayloadMapper;
use App\Searching\ValueObject\Document\SearchDocument;
use App\Searching\ValueObject\Provider\SearchBulkOperation;
use App\Searching\ValueObject\Provider\SearchBulkOperationSet;

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
            $operations[] = $this->buildIndexOperation($document, $indexPrefix);
        }

        return new SearchBulkOperationSet($operations);
    }
}
