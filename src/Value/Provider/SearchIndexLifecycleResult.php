<?php

declare(strict_types=1);

namespace App\Searching\Value\Provider;

final readonly class SearchIndexLifecycleResult
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public string $providerName,
        public string $indexName,
        public string $operation,
        public string $status,
        public bool $changed,
        public array $metadata = [],
    ) {
    }

    public static function unavailable(string $providerName, string $indexName, string $operation, string $reason): self
    {
        return new self(
            providerName: $providerName,
            indexName: $indexName,
            operation: $operation,
            status: 'unavailable',
            changed: false,
            metadata: ['reason' => $reason],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'provider' => $this->providerName,
            'index' => $this->indexName,
            'operation' => $this->operation,
            'status' => $this->status,
            'changed' => $this->changed,
            'metadata' => $this->metadata,
        ];
    }
}
