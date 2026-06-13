<?php

declare(strict_types=1);

namespace App\Searching\Tests\Bridge;

use App\Searching\Service\Bridge\SearchBridgeSurfaceConfigSerializer;
use App\Searching\Service\SearchSurfaceSerializer;
use App\Searching\Value\Bridge\SearchBridgeAutocompleteConfig;
use App\Searching\Value\Bridge\SearchBridgeDegradedState;
use App\Searching\Value\Bridge\SearchBridgeEmptyState;
use App\Searching\Value\Bridge\SearchBridgeResultPageConfig;
use App\Searching\Value\Bridge\SearchBridgeRouteHint;
use App\Searching\Value\Bridge\SearchBridgeSurfaceConfig;
use App\Searching\Value\Surface\SearchSurfaceCapability;
use PHPUnit\Framework\TestCase;

final class SearchBridgeSurfaceConfigSerializerTest extends TestCase
{
    public function testSerializeBridgeConfig(): void
    {
        $serializer = new SearchBridgeSurfaceConfigSerializer(new SearchSurfaceSerializer());
        $config = new SearchBridgeSurfaceConfig(
            capability: new SearchSurfaceCapability(true, 'null', ['query'], ['cataloging'], ['product']),
            autocomplete: new SearchBridgeAutocompleteConfig(true, '/api/search/surface/suggest'),
            resultPage: new SearchBridgeResultPageConfig(true, '/api/search/surface', 'searching_interfacing_results'),
            emptyState: new SearchBridgeEmptyState('No results', 'Try another query.'),
            degradedState: new SearchBridgeDegradedState(false, 'Available', 'Search is available.', 'null'),
            routeHints: [new SearchBridgeRouteHint('searching_interfacing_results', [], 'Results')],
            metadata: ['bridge_name' => 'interfacing_search_bridge'],
        );

        $payload = $serializer->serializeConfig($config);

        self::assertSame('interfacing_search_bridge', $payload['metadata']['bridge_name']);
        self::assertSame('/api/search/surface/suggest', $payload['autocomplete']['endpoint']);
        self::assertSame('searching_interfacing_results', $payload['resultPage']['routeName']);
        self::assertSame('Results', $payload['routeHints'][0]['label']);
    }
}
