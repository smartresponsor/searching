<?php

declare(strict_types=1);

namespace App\Searching\Contract\Provider;

use App\Searching\ValueObject\Provider\SearchProviderResult;
use App\Searching\ValueObject\Provider\SearchProviderStatus;

/**
 * Defines the search backend client interface responsibility within the Searching component runtime and its typed boundaries.
 */
interface SearchBackendClientInterface
{
    /**
     * @param array<string, mixed> $payload
     */
    public function index(string $indexName, string $documentId, array $payload): void;

    /**
     * @param iterable<array{documentId: string, payload: array<string, mixed>}> $documents
     */
    public function bulkIndex(string $indexName, iterable $documents): void;

    /**
     * Deletes the delete through the Searching component mutation boundary.
     */
    public function delete(string $indexName, string $documentId): void;

    /**
     * @param array<string, mixed> $payload
     */
    public function search(string $indexName, array $payload): SearchProviderResult;

    /**
     * Indexes the exists through the Searching component indexing boundary.
     */
    public function indexExists(string $indexName): bool;

    /**
     * @param array<string, mixed> $mapping
     */
    public function createIndex(string $indexName, array $mapping): void;

    /**
     * Deletes the index through the Searching component mutation boundary.
     */
    public function deleteIndex(string $indexName): void;

    /**
     * @param array<string, mixed> $configuration
     */
    public function getStatus(string $providerName, array $configuration = []): SearchProviderStatus;
}
