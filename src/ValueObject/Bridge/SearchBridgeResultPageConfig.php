<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Bridge;

final readonly class SearchBridgeResultPageConfig
{
    /**
     * @param array<string, mixed> $routeParameters
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public bool $enabled,
        public string $endpoint,
        public string $routeName,
        public array $routeParameters = [],
        public string $queryParameter = 'q',
        public string $facetParameter = 'filter',
        public string $pageParameter = 'page',
        public string $limitParameter = 'limit',
        public int $defaultLimit = 20,
        public array $metadata = [],
    ) {
    }
}
