<?php

declare(strict_types=1);

namespace App\Searching\Value\Security;

final readonly class SearchPermissionDecision
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public bool $allowed,
        public string $reason,
        public array $metadata = [],
    ) {
    }

    /** @param array<string, mixed> $metadata */
    public static function allow(string $reason = 'allowed', array $metadata = []): self
    {
        return new self(true, $reason, $metadata);
    }

    /** @param array<string, mixed> $metadata */
    public static function deny(string $reason = 'denied', array $metadata = []): self
    {
        return new self(false, $reason, $metadata);
    }
}
