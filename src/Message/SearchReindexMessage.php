<?php

declare(strict_types=1);

namespace App\Searching\Message;

use App\Searching\ValueObject\Observability\SearchExecutionContext;

/**
 * Messenger-safe request for running a reindex job outside the current HTTP/CLI request.
 */
final readonly class SearchReindexMessage
{
    public function __construct(
        public string $jobKey,
        public ?string $component = null,
        public ?string $resourceType = null,
        public ?string $changedSince = null,
        public ?string $requestedBy = null,
        public ?string $idempotencyKey = null,
        public int $attempt = 1,
        public int $maxAttempts = 3,
        public ?SearchExecutionContext $executionContext = null,
    ) {
    }

    public function getChangedSinceDate(): ?\DateTimeImmutable
    {
        if (null === $this->changedSince || '' === $this->changedSince) {
            return null;
        }

        return new \DateTimeImmutable($this->changedSince);
    }

    public function getDeduplicationKey(): string
    {
        return $this->idempotencyKey ?? hash('sha256', implode('|', [
            $this->component ?? '*',
            $this->resourceType ?? '*',
            $this->changedSince ?? '*',
        ]));
    }
}
