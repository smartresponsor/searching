<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Provider;

/**
 * Defines the search provider status responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchProviderStatus
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public string $nameEntity,
        public bool $available,
        public string $status,
        public array $metadata = [],
    ) {
    }
}
