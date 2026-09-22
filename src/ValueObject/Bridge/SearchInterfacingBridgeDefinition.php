<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Bridge;

/**
 * Searching-owned outbound definition for the Interfacing bridge integration.
 *
 * Searching owns the SearchResponse/SearchBridge payload shape. Bridging owns
 * adaptation into Interfacing's consumer service. Interfacing owns rendering.
 */
final class SearchInterfacingBridgeDefinition
{
    public const BRIDGE_VERSION = 'searching.interfacing.bridge.v1';

    public const INTERFACING_RESULTS_ROUTE = 'interfacing_search_results';
    public const INTERFACING_SUGGEST_ROUTE = 'interfacing_search_suggest';
    public const INTERFACING_CONFIG_ROUTE = 'interfacing_search_config';

    public const INTERFACING_RESULTS_ENDPOINT = '/interfacing/search';
    public const INTERFACING_SUGGEST_ENDPOINT = '/interfacing/search/suggest';
    public const INTERFACING_CONFIG_ENDPOINT = '/interfacing/search/config';

    public const RESPONSE_ENDPOINT = '/api/search/response';
    public const SUGGESTION_ENDPOINT = '/api/search/response/suggest';
    public const CAPABILITY_ENDPOINT = '/api/search/capability';

    public const UI_OWNER = 'interfacing';
    public const ADAPTER_OWNER = 'bridging';
    public const RUNTIME_OWNER = 'searching';

    private function __construct()
    {
    }
}
