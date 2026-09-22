<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Indexing;

/**
 * Defines the search indexed resource state responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchIndexedResourceState
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public string $component,
        public string $resourceType,
        public string $resourceId,
        public ?string $documentHash,
        public ?\DateTimeImmutable $indexedAt,
        public ?\DateTimeImmutable $sourceUpdatedAt,
        public string $status,
        public ?string $errorMessage = null,
        public array $metadata = [],
    ) {
    }

    public function isCurrent(SearchDocumentFingerprint $fingerprint): bool
    {
        return 'indexed' === $this->status
            && $fingerprint->matches($this->documentHash, $this->sourceUpdatedAt);
    }
}
