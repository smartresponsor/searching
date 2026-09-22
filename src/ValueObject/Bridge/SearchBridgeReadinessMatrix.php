<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Bridge;

/**
 * Describes the first stable integration seal between Searching, Bridging, and Interfacing.
 */
final readonly class SearchBridgeReadinessMatrix
{
    /**
     * @param list<SearchBridgeReadinessItem> $items
     */
    public function __construct(
        public string $version,
        public string $summary,
        public array $items,
    ) {
    }

    public static function firstIntegrationSeal(): self
    {
        return new self(
            '0.27',
            'Searching exposes SearchResponse/SearchBridge integration types for Interfacing through Bridging without leaking provider, index, reindex, ledger, or backend internals.',
            [
                new SearchBridgeReadinessItem(
                    'Top search box',
                    'ready',
                    'Interfacing',
                    'SearchBridgeConfig',
                    'GET /api/search/bridge/interfacing',
                    'Interfacing renders the control from bridge metadata and submits to the configured response endpoint.'
                ),
                new SearchBridgeReadinessItem(
                    'Autocomplete',
                    'ready',
                    'Searching',
                    'SearchSuggestionRequest',
                    'GET /api/search/response/suggest',
                    'Bridging forwards UI query parameters and returns SearchSuggestionResponse DTOs only.'
                ),
                new SearchBridgeReadinessItem(
                    'Result page',
                    'ready',
                    'Searching',
                    'SearchResponse',
                    'GET /api/search/response',
                    'Results expose title, summary, highlights, facets, route targets, and metadata safe for UI consumption.'
                ),
                new SearchBridgeReadinessItem(
                    'Empty state',
                    'ready',
                    'Searching',
                    'SearchBridgeEmptyState',
                    'GET /api/search/bridge/interfacing',
                    'The UI receives explicit copy and action metadata instead of inventing fallback text.'
                ),
                new SearchBridgeReadinessItem(
                    'Degraded state',
                    'ready',
                    'Searching',
                    'SearchBridgeDegradedState',
                    'GET /api/search/bridge/interfacing',
                    'Provider outage or disabled backend is represented as a bridge state, not as a UI exception convention.'
                ),
                new SearchBridgeReadinessItem(
                    'Provider internals',
                    'sealed',
                    'Searching',
                    'Interfacing',
                    'SearchResponseProviderInterface',
                    'Interfacing must not depend on SearchProviderInterface, backend payloads, lifecycle, query logs, or reindex jobs.'
                ),
                new SearchBridgeReadinessItem(
                    'Source truth and access',
                    'sealed',
                    'Producer components + Searching',
                    'Interfacing',
                    'hydration + permission filtering',
                    'Backend hits are hydrated and permission-filtered before SearchResponse DTOs are returned.'
                ),
            ]
        );
    }
}
