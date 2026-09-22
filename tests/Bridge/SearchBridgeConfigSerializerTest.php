<?php

declare(strict_types=1);

namespace App\Searching\Tests\Bridge;

use App\Searching\Service\Bridge\SearchBridgeConfigSerializer;
use App\Searching\Service\SearchResponseSerializer;
use App\Searching\ValueObject\Bridge\SearchBridgeAutocompleteConfig;
use App\Searching\ValueObject\Bridge\SearchBridgeConfig;
use App\Searching\ValueObject\Bridge\SearchBridgeDegradedState;
use App\Searching\ValueObject\Bridge\SearchBridgeEmptyState;
use App\Searching\ValueObject\Bridge\SearchBridgeResultPageConfig;
use App\Searching\ValueObject\Bridge\SearchBridgeRouteHint;
use App\Searching\ValueObject\Provider\SearchCapability;
use PHPUnit\Framework\TestCase;

final class SearchBridgeConfigSerializerTest extends TestCase
{
    public function testSerializeBridgeConfig(): void
    {
        $serializer = new SearchBridgeConfigSerializer(new SearchResponseSerializer());
        $config = new SearchBridgeConfig(
            capability: new SearchCapability(true, 'null', ['query'], ['cataloging'], ['product']),
            autocomplete: new SearchBridgeAutocompleteConfig(true, '/api/search/response/suggest'),
            resultPage: new SearchBridgeResultPageConfig(true, '/api/search/response', 'searching_interfacing_results'),
            emptyState: new SearchBridgeEmptyState('No results', 'Try another query.'),
            degradedState: new SearchBridgeDegradedState(false, 'Available', 'Search is available.', 'null'),
            routeHints: [new SearchBridgeRouteHint('searching_interfacing_results', [], 'Results')],
            metadata: ['bridge_name' => 'interfacing_search_bridge'],
        );

        $payload = $serializer->serializeConfig($config);
        /** @var array{metadata: array{bridge_name: string}, autocomplete: array{endpoint: string}, resultPage: array{routeName: string}, routeHints: list<array{label: string}>} $payload */
        self::assertSame('interfacing_search_bridge', $payload['metadata']['bridge_name']);
        self::assertSame('/api/search/response/suggest', $payload['autocomplete']['endpoint']);
        self::assertSame('searching_interfacing_results', $payload['resultPage']['routeName']);
        self::assertSame('Results', $payload['routeHints'][0]['label']);
    }
}
