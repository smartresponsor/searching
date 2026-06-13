<?php

declare(strict_types=1);

namespace App\Searching\Value\Flow;

final readonly class SearchOperationLimitDecision
{
    public function __construct(
        public bool $allowed,
        public bool $deferred = false,
        public ?int $retryAfterSeconds = null,
        public ?string $reason = null,
        public array $metadata = [],
    ) {
    }

    public static function allow(array $metadata = []): self
    {
        return new self(true, false, null, null, $metadata);
    }

    public static function reject(string $reason, ?int $retryAfterSeconds = null, array $metadata = []): self
    {
        return new self(false, false, $retryAfterSeconds, $reason, $metadata);
    }

    public static function defer(string $reason, ?int $retryAfterSeconds = null, array $metadata = []): self
    {
        return new self(false, true, $retryAfterSeconds, $reason, $metadata);
    }

    public function status(): string
    {
        if ($this->allowed) {
            return 'allowed';
        }

        return $this->deferred ? 'deferred' : 'rejected';
    }

    /**
     * @return array<string, mixed>
     */
    public function toMetadata(): array
    {
        return [
            'status' => $this->status(),
            'allowed' => $this->allowed,
            'deferred' => $this->deferred,
            'retry_after_seconds' => $this->retryAfterSeconds,
            'reason' => $this->reason,
        ] + $this->metadata;
    }
}
