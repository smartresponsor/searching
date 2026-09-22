<?php

declare(strict_types=1);

namespace App\Searching\Provider\Bridge;

use App\Searching\Contract\Bridge\SearchInterfacingBridgeProviderInterface;
use App\Searching\Contract\Query\SearchResponseProviderInterface;
use App\Searching\Contract\Query\SearchSuggestionResponseProviderInterface;
use App\Searching\ValueObject\Bridge\SearchBridgeAutocompleteConfig;
use App\Searching\ValueObject\Bridge\SearchBridgeConfig;
use App\Searching\ValueObject\Bridge\SearchBridgeDegradedState;
use App\Searching\ValueObject\Bridge\SearchBridgeEmptyState;
use App\Searching\ValueObject\Bridge\SearchBridgeResultPageConfig;
use App\Searching\ValueObject\Bridge\SearchBridgeRouteHint;
use App\Searching\ValueObject\Bridge\SearchInterfacingBridgeDefinition;
use App\Searching\ValueObject\Query\SearchQueryRequest;
use App\Searching\ValueObject\Query\SearchSuggestionRequest;
use App\Searching\ValueObject\Result\SearchResponse;

/**
 * Defines the search interfacing bridge provider responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchInterfacingBridgeProvider implements SearchInterfacingBridgeProviderInterface
{
    public function __construct(
        private SearchResponseProviderInterface $responseProvider,
        private SearchSuggestionResponseProviderInterface $suggestionProvider,
    ) {
    }

    public function getBridgeConfig(): SearchBridgeConfig
    {
        $capability = $this->responseProvider->getCapability();
        $degraded = !$capability->enabled;

        return new SearchBridgeConfig(
            capability: $capability,
            autocomplete: new SearchBridgeAutocompleteConfig(
                enabled: $capability->enabled,
                endpoint: SearchInterfacingBridgeDefinition::INTERFACING_SUGGEST_ENDPOINT,
                minQueryLength: 2,
                limit: 10,
                placeholder: 'Search across connected business data…',
                metadata: [
                    'bridge_profile' => 'interfacing.global_search.autocomplete.v1',
                    'supported_components' => $capability->supportedComponents,
                    'supported_resource_types' => $capability->supportedResourceTypes,
                ],
            ),
            resultPage: new SearchBridgeResultPageConfig(
                enabled: true,
                endpoint: SearchInterfacingBridgeDefinition::INTERFACING_RESULTS_ENDPOINT,
                routeName: SearchInterfacingBridgeDefinition::INTERFACING_RESULTS_ROUTE,
                defaultLimit: 20,
                metadata: [
                    'bridge_profile' => 'interfacing.global_search.results.v1',
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
                    ? 'Search can still render a safe UI response, but the active backend provider is not available.'
                    : 'The active search provider is available for UI consumption.',
                providerName: $capability->providerName,
                metadata: $capability->metadata,
            ),
            routeHints: [
                new SearchBridgeRouteHint(SearchInterfacingBridgeDefinition::INTERFACING_RESULTS_ROUTE, [], 'Interfacing search results'),
                new SearchBridgeRouteHint(SearchInterfacingBridgeDefinition::INTERFACING_SUGGEST_ROUTE, [], 'Interfacing autocomplete'),
                new SearchBridgeRouteHint('searching_api_response_search', [], 'Searching response API'),
                new SearchBridgeRouteHint('searching_api_response_suggest', [], 'Searching autocomplete API'),
                new SearchBridgeRouteHint('searching_api_capability', [], 'Searching capability API'),
            ],
            metadata: [
                'bridge_name' => 'interfacing_search_bridge',
                'bridge_version' => SearchInterfacingBridgeDefinition::BRIDGE_VERSION,
                'ui_owner' => SearchInterfacingBridgeDefinition::UI_OWNER,
                'adapter_owner' => SearchInterfacingBridgeDefinition::ADAPTER_OWNER,
                'runtime_owner' => SearchInterfacingBridgeDefinition::RUNTIME_OWNER,
            ],
        );
    }

    /**
     * Searches the search through the Searching component query boundary.
     */
    public function search(SearchQueryRequest $query): SearchResponse
    {
        return $this->responseProvider->search($query);
    }

    /**
     * Builds suggestions for the suggest through the Searching component query boundary.
     */
    public function suggest(SearchSuggestionRequest $query): array
    {
        return $this->suggestionProvider->suggest($query);
    }
}
