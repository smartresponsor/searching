<?php

declare(strict_types=1);

namespace App\Searching\Tests\Entity;

use App\Searching\Entity\SearchQueryLogEntity;
use App\Searching\Value\Query\SearchQueryExecutionTrace;
use PHPUnit\Framework\TestCase;

final class SearchQueryLogTest extends TestCase
{
    public function testCreateFromTrace(): void
    {
        $log = SearchQueryLogEntity::fromTrace(new SearchQueryExecutionTrace(
            query: 'order',
            userId: 'user-1',
            tenantId: 'tenant-1',
            providerName: 'opensearch',
            providerTotal: 10,
            returnedTotal: 7,
            deniedCount: 3,
            durationMs: 9.5,
            executedAt: new \DateTimeImmutable('2026-05-27T00:00:00+00:00'),
            metadata: ['limit' => 20],
        ));

        self::assertSame('order', $log->getQueryText());
        self::assertSame('opensearch', $log->getProviderName());
        self::assertSame(10, $log->getProviderTotal());
        self::assertSame(7, $log->getReturnedTotal());
        self::assertSame(3, $log->getDeniedCount());
        self::assertSame(9.5, $log->getDurationMs());
        self::assertTrue($log->isSuccessful());
        self::assertSame(['limit' => 20], $log->getMetadata()['metadata']);
    }
}
