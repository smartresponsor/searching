<?php

declare(strict_types=1);

namespace App\Searching\Value\Result;

final readonly class SearchResultItem
{
    /**
     * @param array<string, mixed>  $routeParameters
     * @param list<SearchHighlight> $highlights
     * @param array<string, mixed>  $metadata
     */
    public function __construct(
        public string $component,
        public string $resourceType,
        public string $resourceId,
        public string $title,
        public ?string $summary,
        public string $routeName,
        public array $routeParameters,
        public float $score = 0.0,
        public array $highlights = [],
        public array $metadata = [],
    ) {
    }
}
