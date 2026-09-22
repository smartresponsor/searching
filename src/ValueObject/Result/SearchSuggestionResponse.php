<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Result;

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
