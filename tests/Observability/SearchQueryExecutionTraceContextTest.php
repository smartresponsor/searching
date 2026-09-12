<?php

declare(strict_types=1);

namespace App\Searching\Tests\Observability;

use App\Searching\Entity\SearchQueryLogEntity;
use App\Searching\Service\Serialization\SearchQueryExecutionTraceSerializer;
use App\Searching\Value\Observability\SearchExecutionContext;
use App\Searching\Value\Query\SearchQueryExecutionTrace;
use PHPUnit\Framework\TestCase;

final class SearchQueryExecutionTraceContextTest extends TestCase
{
    public function testTraceContextIsSerializedAndPersistedIntoLogShape(): void
    {
        $context = SearchExecutionContext::create('corr-1', 'req-1', 'interfacing', 'search.query', 'user-1');
        $trace = new SearchQueryExecutionTrace(
            query: 'phone',
            userId: 'user-1',
            tenantId: 'tenant-1',
            providerName: 'opensearch',
            providerTotal: 3,
            returnedTotal: 2,
            deniedCount: 1,
            durationMs: 4.5,
            executedAt: new \DateTimeImmutable('2026-05-27T00:00:00+00:00'),
            executionContext: $context,
        );

        $payload = (new SearchQueryExecutionTraceSerializer())->serialize($trace);
        /** @var array{executionContext: array{correlation_id: string}} $payload */
        $log = SearchQueryLogEntity::fromTrace($trace);

        self::assertSame('corr-1', $payload['executionContext']['correlation_id']);
        self::assertSame('corr-1', $log->getCorrelationId());
        self::assertSame('req-1', $log->getRequestId());
        self::assertSame('interfacing', $log->getSourceComponent());
        self::assertSame('search.query', $log->getSourceOperation());
    }
}
