<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Bridge;

use App\Searching\ValueObject\Provider\SearchCapability;

/**
 * Defines the search bridge config responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchBridgeConfig
{
    /**
     * @param list<SearchBridgeRouteHint> $routeHints
     * @param array<string, mixed>        $metadata
     */
    public function __construct(
        public SearchCapability $capability,
        public SearchBridgeAutocompleteConfig $autocomplete,
        public SearchBridgeResultPageConfig $resultPage,
        public SearchBridgeEmptyState $emptyState,
        public SearchBridgeDegradedState $degradedState,
        public array $routeHints = [],
        public array $metadata = [],
    ) {
    }
}
