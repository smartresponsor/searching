<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Bridge;

/**
 * Defines the search bridge autocomplete config responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchBridgeAutocompleteConfig
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public bool $enabled,
        public string $endpoint,
        public string $queryParameter = 'q',
        public int $minQueryLength = 2,
        public int $limit = 10,
        public string $placeholder = 'Search…',
        public array $metadata = [],
    ) {
    }
}
