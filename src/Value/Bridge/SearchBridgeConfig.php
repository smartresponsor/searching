<?php

declare(strict_types=1);

namespace App\Searching\Value\Bridge;

use App\Searching\Value\Provider\SearchCapability;

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
