<?php

declare(strict_types=1);

namespace App\Searching\Contract;

/**
 * Searching-owned outbound contract for the Interfacing bridge surface.
 *
 * Searching owns the SearchSurface/SearchBridge payload shape. Bridging owns
 * adaptation into Interfacing's consumer service. Interfacing owns rendering.
 */
final class SearchInterfacingBridgeSurfaceContract
{
    public const CONTRACT_VERSION = 'searching.interfacing.bridge.v1';

    public const INTERFACING_RESULTS_ROUTE = 'interfacing_search_results';
    public const INTERFACING_SUGGEST_ROUTE = 'interfacing_search_suggest';
    public const INTERFACING_CONFIG_ROUTE = 'interfacing_search_config';

    public const INTERFACING_RESULTS_ENDPOINT = '/interfacing/search';
    public const INTERFACING_SUGGEST_ENDPOINT = '/interfacing/search/suggest';
    public const INTERFACING_CONFIG_ENDPOINT = '/interfacing/search/config';

    public const SURFACE_ENDPOINT = '/api/search/surface';
    public const SUGGESTION_ENDPOINT = '/api/search/surface/suggest';
    public const CAPABILITY_ENDPOINT = '/api/search/surface/capability';

    public const UI_OWNER = 'interfacing';
    public const ADAPTER_OWNER = 'bridging';
    public const RUNTIME_OWNER = 'searching';

    private function __construct()
    {
    }
}
