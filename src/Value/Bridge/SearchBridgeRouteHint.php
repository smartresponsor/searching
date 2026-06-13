<?php

declare(strict_types=1);

namespace App\Searching\Value\Bridge;

final readonly class SearchBridgeRouteHint
{
    /**
     * @param array<string, mixed> $routeParameters
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public string $routeName,
        public array $routeParameters = [],
        public ?string $label = null,
        public array $metadata = [],
    ) {
    }
}
