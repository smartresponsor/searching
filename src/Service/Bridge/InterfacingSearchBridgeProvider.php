<?php

declare(strict_types=1);

namespace App\Searching\Service\Bridge;

use App\Searching\Contract\SearchInterfacingBridgeSurfaceContract;
use App\Searching\ServiceInterface\Bridge\InterfacingSearchBridgeProviderInterface;
use App\Searching\ServiceInterface\Surface\SearchSuggestionSurfaceProviderInterface;
use App\Searching\ServiceInterface\Surface\SearchSurfaceProviderInterface;
use App\Searching\Value\Bridge\SearchBridgeAutocompleteConfig;
use App\Searching\Value\Bridge\SearchBridgeDegradedState;
use App\Searching\Value\Bridge\SearchBridgeEmptyState;
use App\Searching\Value\Bridge\SearchBridgeResultPageConfig;
use App\Searching\Value\Bridge\SearchBridgeRouteHint;
use App\Searching\Value\Bridge\SearchBridgeSurfaceConfig;
use App\Searching\Value\Surface\SearchSurfaceQuery;
use App\Searching\Value\Surface\SearchSurfaceResult;
use App\Searching\Value\Surface\SearchSurfaceSuggestionQuery;

final readonly class InterfacingSearchBridgeProvider implements InterfacingSearchBridgeProviderInterface
{
    public function __construct(
        private SearchSurfaceProviderInterface $surfaceProvider,
        private SearchSuggestionSurfaceProviderInterface $suggestionProvider,
    ) {
    }

    public function getSurfaceConfig(): SearchBridgeSurfaceConfig
    {
        $capability = $this->surfaceProvider->getCapability();
        $degraded = !$capability->enabled;

        return new SearchBridgeSurfaceConfig(
            capability: $capability,
            autocomplete: new SearchBridgeAutocompleteConfig(
                enabled: $capability->enabled,
                endpoint: SearchInterfacingBridgeSurfaceContract::INTERFACING_SUGGEST_ENDPOINT,
                minQueryLength: 2,
                limit: 10,
                placeholder: 'Search across connected business data…',
                metadata: [
                    'bridge_contract' => 'interfacing.global_search.autocomplete.v1',
                    'supported_components' => $capability->supportedComponents,
                    'supported_resource_types' => $capability->supportedResourceTypes,
                ],
            ),
            resultPage: new SearchBridgeResultPageConfig(
                enabled: true,
                endpoint: SearchInterfacingBridgeSurfaceContract::INTERFACING_RESULTS_ENDPOINT,
                routeName: SearchInterfacingBridgeSurfaceContract::INTERFACING_RESULTS_ROUTE,
                defaultLimit: 20,
                metadata: [
                    'bridge_contract' => 'interfacing.global_search.results.v1',
                    'facets_enabled' => in_array('facets', $capability->supportedFeatures, true),
                    'highlights_enabled' => in_array('highlights', $capability->supportedFeatures, true),
                ],
            ),
            emptyState: new SearchBridgeEmptyState(
                title: 'No search results found',
                message: 'Try a different keyword, remove filters, or search another connected resource.',
                metadata: ['state' => 'empty_results'],
            ),
            degradedState: new SearchBridgeDegradedState(
                degraded: $degraded,
                title: $degraded ? 'Search is running in degraded mode' : 'Search is available',
                message: $degraded
                    ? 'Search can still render a safe UI surface, but the active backend provider is not available.'
                    : 'The active search provider is available for UI consumption.',
                providerName: $capability->providerName,
                metadata: $capability->metadata,
            ),
            routeHints: [
                new SearchBridgeRouteHint(SearchInterfacingBridgeSurfaceContract::INTERFACING_RESULTS_ROUTE, [], 'Interfacing search results'),
                new SearchBridgeRouteHint(SearchInterfacingBridgeSurfaceContract::INTERFACING_SUGGEST_ROUTE, [], 'Interfacing autocomplete'),
                new SearchBridgeRouteHint('searching_api_surface_search', [], 'Searching surface API'),
                new SearchBridgeRouteHint('searching_api_surface_suggest', [], 'Searching autocomplete API'),
                new SearchBridgeRouteHint('searching_api_surface_capability', [], 'Searching capability API'),
            ],
            metadata: [
                'bridge_name' => 'interfacing_search_bridge',
                'contract_version' => SearchInterfacingBridgeSurfaceContract::CONTRACT_VERSION,
                'ui_owner' => SearchInterfacingBridgeSurfaceContract::UI_OWNER,
                'adapter_owner' => SearchInterfacingBridgeSurfaceContract::ADAPTER_OWNER,
                'runtime_owner' => SearchInterfacingBridgeSurfaceContract::RUNTIME_OWNER,
            ],
        );
    }

    public function search(SearchSurfaceQuery $query): SearchSurfaceResult
    {
        return $this->surfaceProvider->search($query);
    }

    public function suggest(SearchSurfaceSuggestionQuery $query): array
    {
        return $this->suggestionProvider->suggest($query);
    }
}
