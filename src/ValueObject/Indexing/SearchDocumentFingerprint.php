<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Indexing;

/**
 * Defines the search document fingerprint responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchDocumentFingerprint
{
    public function __construct(
        public string $component,
        public string $resourceType,
        public string $resourceId,
        public string $documentHash,
        public \DateTimeImmutable $sourceUpdatedAt,
    ) {
    }

    /**
     * Executes the matches responsibility defined by the Searching component contract.
     */
    public function matches(?string $knownHash, ?\DateTimeImmutable $knownSourceUpdatedAt): bool
    {
        return $knownHash === $this->documentHash
            && $knownSourceUpdatedAt?->getTimestamp() === $this->sourceUpdatedAt->getTimestamp();
    }
}
