<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Result;

/**
 * Defines the search suggestion response responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchSuggestionResponse
{
    /**
     * @param array<string, mixed> $routeParameters
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public string $text,
        public float $score = 0.0,
        public ?string $component = null,
        public ?string $resourceType = null,
        public ?string $resourceId = null,
        public ?string $routeName = null,
        public array $routeParameters = [],
        public array $metadata = [],
    ) {
    }
}
