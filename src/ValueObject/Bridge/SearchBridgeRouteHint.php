<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Bridge;

/**
 * Defines the search bridge route hint responsibility within the Searching component runtime and its typed boundaries.
 */
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
