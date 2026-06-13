<?php

declare(strict_types=1);

namespace App\Searching\Value\Indexing;

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

    public function matches(?string $knownHash, ?\DateTimeImmutable $knownSourceUpdatedAt): bool
    {
        return $knownHash === $this->documentHash
            && $knownSourceUpdatedAt?->getTimestamp() === $this->sourceUpdatedAt->getTimestamp();
    }
}
