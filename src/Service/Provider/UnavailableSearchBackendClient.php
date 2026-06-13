<?php

declare(strict_types=1);

namespace App\Searching\Service\Provider;

use App\Searching\ServiceInterface\Provider\SearchBackendClientInterface;
use App\Searching\Value\Provider\SearchProviderResult;
use App\Searching\Value\Provider\SearchProviderStatus;

final class UnavailableSearchBackendClient implements SearchBackendClientInterface
{
    public function index(string $indexName, string $documentId, array $payload): void
    {
        // Intentionally no-op. This backend is used when no real client is configured.
    }

    public function bulkIndex(string $indexName, iterable $documents): void
    {
        foreach ($documents as $_document) {
            // Consume the iterable so producer-side generator errors still surface during reindexing.
        }
    }

    public function delete(string $indexName, string $documentId): void
    {
        // Intentionally no-op.
    }

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

    public function indexExists(string $indexName): bool
    {
        return false;
    }

    public function createIndex(string $indexName, array $mapping): void
    {
        // Intentionally no-op. A real backend client must create physical indexes.
    }

    public function deleteIndex(string $indexName): void
    {
        // Intentionally no-op. A real backend client must delete physical indexes.
    }

    public function getStatus(string $providerName, array $configuration = []): SearchProviderStatus
    {
        return new SearchProviderStatus(
            name: $providerName,
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
