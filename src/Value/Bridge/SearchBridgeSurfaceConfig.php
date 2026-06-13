<?php

declare(strict_types=1);

namespace App\Searching\Value\Bridge;

use App\Searching\Value\Surface\SearchSurfaceCapability;

final readonly class SearchBridgeSurfaceConfig
{
    /**
     * @param list<SearchBridgeRouteHint> $routeHints
     * @param array<string, mixed>        $metadata
     */
    public function __construct(
        public SearchSurfaceCapability $capability,
        public SearchBridgeAutocompleteConfig $autocomplete,
        public SearchBridgeResultPageConfig $resultPage,
        public SearchBridgeEmptyState $emptyState,
        public SearchBridgeDegradedState $degradedState,
        public array $routeHints = [],
        public array $metadata = [],
    ) {
    }
}
