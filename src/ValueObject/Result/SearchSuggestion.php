<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Result;

/**
 * Defines the search suggestion responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchSuggestion
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public string $text,
        public float $score = 0.0,
        public ?string $component = null,
        public ?string $resourceType = null,
        public ?string $resourceId = null,
        public array $metadata = [],
    ) {
    }
}
