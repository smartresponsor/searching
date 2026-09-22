<?php

declare(strict_types=1);

namespace App\Searching\Provider\Backend;

use App\Searching\Builder\Provider\SearchIndexNameBuilder;
use App\Searching\Contract\Provider\SearchBackendClientInterface;
use App\Searching\Contract\Provider\SearchBackendQueryBuilderInterface;
use App\Searching\Contract\Provider\SearchBackendSuggestionBuilderInterface;
use App\Searching\Contract\Provider\SearchBulkOperationBuilderInterface;
use App\Searching\Contract\Provider\SearchIndexLifecycleProviderInterface;
use App\Searching\Contract\Provider\SearchIndexMappingBuilderInterface;
use App\Searching\Contract\Provider\SearchProviderInterface;
use App\Searching\ValueObject\Document\SearchDocument;
use App\Searching\ValueObject\Provider\SearchIndexLifecycleResult;
use App\Searching\ValueObject\Provider\SearchProviderConfiguration;
use App\Searching\ValueObject\Provider\SearchProviderResult;
use App\Searching\ValueObject\Provider\SearchProviderStatus;
use App\Searching\ValueObject\Query\SearchQuery;
use App\Searching\ValueObject\Query\SearchSuggestionQuery;

abstract class SearchAbstractBackendProvider implements SearchProviderInterface, SearchIndexLifecycleProviderInterface
{
    public function __construct(
        private readonly SearchProviderConfiguration $configuration,
        private readonly SearchIndexNameBuilder $indexNameBuilder,
        private readonly SearchBackendQueryBuilderInterface $queryBuilder,
        private readonly SearchBackendSuggestionBuilderInterface $suggestionBuilder,
        private readonly SearchBulkOperationBuilderInterface $bulkOperationBuilder,
        private readonly SearchIndexMappingBuilderInterface $mappingBuilder,
        private readonly SearchBackendClientInterface $backendClient,
    ) {
    }

    public function index(SearchDocument $document): void
    {
        $operation = $this->bulkOperationBuilder->buildIndexOperation($document, $this->configuration->indexPrefix);

        $this->backendClient->index(
            indexName: $operation->indexName,
            documentId: $operation->documentId,
            payload: $operation->payload,
        );
    }

    public function bulkIndex(iterable $documents): void
    {
        $operationSet = $this->bulkOperationBuilder->buildIndexOperations($documents, $this->configuration->indexPrefix);

        foreach ($operationSet->groupedByIndex() as $indexName => $operations) {
            $this->backendClient->bulkIndex(
                $indexName,
                array_map(
                    static fn ($operation): array => [
                        'documentId' => $operation->documentId,
                        'payload' => $operation->payload,
                    ],
                    $operations,
                ),
            );
        }
    }

    public function delete(string $component, string $resourceType, string $resourceId): void
    {
        $indexName = $this->buildIndexName($component, $resourceType);
        $documentId = $this->buildDocumentId($component, $resourceType, $resourceId);

        $this->backendClient->delete($indexName, $documentId);
    }

    public function search(SearchQuery $query): SearchProviderResult
    {
        return $this->backendClient->search(
            indexName: $this->indexNameBuilder->buildForQuery($query, $this->configuration->indexPrefix),
            payload: $this->queryBuilder->build($query, $this->configuration)->toPayload(),
        );
    }

    public function suggest(SearchSuggestionQuery $query): array
    {
        $result = $this->backendClient->search(
            indexName: $this->indexNameBuilder->buildForSuggestion($query, $this->configuration->indexPrefix),
            payload: $this->suggestionBuilder->build($query, $this->configuration)->toPayload(),
        );

        if ([] !== $result->suggestions) {
            return $result->suggestions;
        }

        return array_map(
            static fn ($item) => new \App\Searching\ValueObject\Result\SearchSuggestion(
                text: $item->title,
                score: $item->score,
                component: $item->component,
                resourceType: $item->resourceType,
                resourceId: $item->resourceId,
                metadata: $item->metadata,
            ),
            $result->items,
        );
    }

    public function getStatus(): SearchProviderStatus
    {
        return $this->backendClient->getStatus($this->configuration->nameEntity, $this->configuration->toBackendConfiguration());
    }

    public function indexExists(string $component, string $resourceType): bool
    {
        return $this->backendClient->indexExists($this->buildIndexName($component, $resourceType));
    }

    public function ensureIndex(string $component, string $resourceType): SearchIndexLifecycleResult
    {
        $indexName = $this->buildIndexName($component, $resourceType);
        $status = $this->getStatus();

        if (!$status->available) {
            return SearchIndexLifecycleResult::unavailable(
                providerName: $this->configuration->nameEntity,
                indexName: $indexName,
                operation: 'ensure',
                reason: $this->statusReason($status),
            );
        }

        if ($this->backendClient->indexExists($indexName)) {
            return new SearchIndexLifecycleResult(
                providerName: $this->configuration->nameEntity,
                indexName: $indexName,
                operation: 'ensure',
                status: 'exists',
                changed: false,
            );
        }

        $mapping = $this->mappingBuilder->build($indexName, $component, $resourceType, $this->configuration);
        $this->backendClient->createIndex($indexName, $mapping->toPayload());

        return new SearchIndexLifecycleResult(
            providerName: $this->configuration->nameEntity,
            indexName: $indexName,
            operation: 'ensure',
            status: 'created',
            changed: true,
            metadata: ['mapping' => $mapping->metadata],
        );
    }

    public function deleteIndex(string $component, string $resourceType): SearchIndexLifecycleResult
    {
        $indexName = $this->buildIndexName($component, $resourceType);
        $status = $this->getStatus();

        if (!$status->available) {
            return SearchIndexLifecycleResult::unavailable(
                providerName: $this->configuration->nameEntity,
                indexName: $indexName,
                operation: 'delete',
                reason: $this->statusReason($status),
            );
        }

        if (!$this->backendClient->indexExists($indexName)) {
            return new SearchIndexLifecycleResult(
                providerName: $this->configuration->nameEntity,
                indexName: $indexName,
                operation: 'delete',
                status: 'missing',
                changed: false,
            );
        }

        $this->backendClient->deleteIndex($indexName);

        return new SearchIndexLifecycleResult(
            providerName: $this->configuration->nameEntity,
            indexName: $indexName,
            operation: 'delete',
            status: 'deleted',
            changed: true,
        );
    }

    private function statusReason(SearchProviderStatus $status): string
    {
        $reason = $status->metadata['reason'] ?? null;

        return is_string($reason) && '' !== trim($reason) ? $reason : 'Search backend is not available.';
    }

    protected function buildIndexName(string $component, string $resourceType): string
    {
        return $this->indexNameBuilder->buildForParts($this->configuration->indexPrefix, $component, $resourceType);
    }

    protected function buildDocumentId(string $component, string $resourceType, string $resourceId): string
    {
        return $this->indexNameBuilder->buildDocumentIdForParts($component, $resourceType, $resourceId);
    }
}
