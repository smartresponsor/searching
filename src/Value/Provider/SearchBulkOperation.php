<?php

declare(strict_types=1);

namespace App\Searching\Value\Provider;

final readonly class SearchBulkOperation
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public string $operation,
        public string $indexName,
        public string $documentId,
        public array $payload = [],
    ) {
        if (!in_array($operation, ['index', 'delete'], true)) {
            throw new \InvalidArgumentException('Search bulk operation must be "index" or "delete".');
        }
    }

    /**
     * @return array{operation: string, indexName: string, documentId: string, payload: array<string, mixed>}
     */
    public function toArray(): array
    {
        return [
            'operation' => $this->operation,
            'indexName' => $this->indexName,
            'documentId' => $this->documentId,
            'payload' => $this->payload,
        ];
    }
}
