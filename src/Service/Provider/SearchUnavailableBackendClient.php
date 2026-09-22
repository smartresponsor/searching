<?php

declare(strict_types=1);

namespace App\Searching\Service\Provider;

use App\Searching\Contract\Provider\SearchBackendClientInterface;
use App\Searching\ValueObject\Provider\SearchProviderResult;
use App\Searching\ValueObject\Provider\SearchProviderStatus;

/**
 * Defines the search unavailable backend client responsibility within the Searching component runtime and its typed boundaries.
 */
final class SearchUnavailableBackendClient implements SearchBackendClientInterface
{
    /**
     * Indexes the index through the Searching component indexing boundary.
     */
    public function index(string $indexName, string $documentId, array $payload): void
    {
        // Intentionally no-op. This backend is used when no real client is configured.
    }

    /**
     * Executes the bulk index responsibility defined by the Searching component contract.
     */
    public function bulkIndex(string $indexName, iterable $documents): void
    {
        foreach ($documents as $_document) {
            // Consume the iterable so producer-side generator errors still surface during reindexing.
        }
    }

    /**
     * Deletes the delete through the Searching component mutation boundary.
     */
    public function delete(string $indexName, string $documentId): void
    {
        // Intentionally no-op.
    }

    /**
     * Searches the search through the Searching component query boundary.
     */
    public function search(string $indexName, array $payload): SearchProviderResult
    {
        return new SearchProviderResult(
            total: 0,
            items: [],
            facets: [],
            suggestions: [],
            metadata: [
                'provider_mode' => 'unavailable',
                'index' => $indexName,
                'reason' => 'No backend client is configured for this provider.',
            ],
        );
    }

    /**
     * Indexes the exists through the Searching component indexing boundary.
     */
    public function indexExists(string $indexName): bool
    {
        return false;
    }

    /**
     * Creates the index required by the Searching component runtime flow.
     */
    public function createIndex(string $indexName, array $mapping): void
    {
        // Intentionally no-op. A real backend client must create physical indexes.
    }

    /**
     * Deletes the index through the Searching component mutation boundary.
     */
    public function deleteIndex(string $indexName): void
    {
        // Intentionally no-op. A real backend client must delete physical indexes.
    }

    public function getStatus(string $providerName, array $configuration = []): SearchProviderStatus
    {
        return new SearchProviderStatus(
            nameEntity: $providerName,
            available: false,
            status: 'unavailable',
            metadata: [
                'enabled' => (bool) ($configuration['enabled'] ?? false),
                'dsnConfigured' => isset($configuration['dsn']) && is_string($configuration['dsn']) && '' !== $configuration['dsn'],
                'indexPrefix' => $configuration['index_prefix'] ?? null,
                'reason' => 'No real search backend client has been wired yet.',
            ],
        );
    }
}
