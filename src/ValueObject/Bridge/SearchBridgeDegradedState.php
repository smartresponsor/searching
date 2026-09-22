<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Bridge;

/**
 * Defines the search bridge degraded state responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchBridgeDegradedState
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public bool $degraded,
        public string $title,
        public string $message,
        public ?string $providerName = null,
        public array $metadata = [],
    ) {
    }
}
