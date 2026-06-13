<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service\Serialization;

use App\Searching\Service\Serialization\SearchQueryExecutionTraceSerializer;
use App\Searching\Value\Query\SearchQueryExecutionTrace;
use PHPUnit\Framework\TestCase;

final class SearchQueryExecutionTraceSerializerTest extends TestCase
{
    public function testSerializeTrace(): void
    {
        $serializer = new SearchQueryExecutionTraceSerializer();
        $payload = $serializer->serialize(new SearchQueryExecutionTrace(
            query: 'invoice',
            userId: 'user-1',
            tenantId: 'tenant-1',
            providerName: 'null',
            providerTotal: 3,
            returnedTotal: 2,
            deniedCount: 1,
            durationMs: 12.345,
            executedAt: new \DateTimeImmutable('2026-05-27T00:00:00+00:00'),
            providerMetadata: ['backend' => 'unavailable'],
            metadata: ['page' => 1],
        ));

        self::assertSame('invoice', $payload['query']);
        self::assertSame('null', $payload['providerName']);
        self::assertSame(3, $payload['providerTotal']);
        self::assertSame(2, $payload['returnedTotal']);
        self::assertSame(1, $payload['deniedCount']);
        self::assertTrue($payload['successful']);
    }
}
