<?php

declare(strict_types=1);

namespace App\Searching\Contract\Provider;

use App\Searching\ValueObject\Provider\SearchProviderResult;
use App\Searching\ValueObject\Provider\SearchProviderStatus;

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

    public function delete(string $indexName, string $documentId): void;

    /**
     * @param array<string, mixed> $payload
     */
    public function search(string $indexName, array $payload): SearchProviderResult;

    public function indexExists(string $indexName): bool;

    /**
     * @param array<string, mixed> $mapping
     */
    public function createIndex(string $indexName, array $mapping): void;

    public function deleteIndex(string $indexName): void;

    /**
     * @param array<string, mixed> $configuration
     */
    public function getStatus(string $providerName, array $configuration = []): SearchProviderStatus;
}
