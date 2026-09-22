<?php

declare(strict_types=1);

namespace App\Searching\Tests;

use App\Searching\Service\Bridge\SearchBridgeReadinessMatrixSerializer;
use App\Searching\ValueObject\Bridge\SearchBridgeReadinessMatrix;
use PHPUnit\Framework\TestCase;

final class SearchBridgeReadinessMatrixSerializerTest extends TestCase
{
    public function testSerializesFirstIntegrationSeal(): void
    {
        $payload = (new SearchBridgeReadinessMatrixSerializer())
            ->serialize(SearchBridgeReadinessMatrix::firstIntegrationSeal());

        self::assertSame('0.27', $payload['version']);
        self::assertNotEmpty($payload['items']);
        self::assertSame('Top search box', $payload['items'][0]['area']);
        self::assertSame('ready', $payload['items'][0]['status']);
        self::assertStringContainsString('SearchResponse/SearchBridge', $payload['summary']);
    }
}
