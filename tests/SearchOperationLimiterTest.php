<?php

declare(strict_types=1);

namespace App\Searching\Tests;

use App\Searching\Service\Flow\SearchOperationLimiter;
use App\Searching\ValueObject\Flow\SearchOperationLimitRequest;
use PHPUnit\Framework\TestCase;

final class SearchOperationLimiterTest extends TestCase
{
    public function testRejectsWhenOperationCostExceedsRemainingWindowBudget(): void
    {
        $limiter = new SearchOperationLimiter([
            'search.query' => ['limit' => 2, 'window_seconds' => 60, 'mode' => 'reject'],
        ]);

        $first = $limiter->decide(new SearchOperationLimitRequest('search.query', 'user-1', 1));
        $second = $limiter->decide(new SearchOperationLimitRequest('search.query', 'user-1', 2));

        self::assertTrue($first->allowed);
        self::assertFalse($second->allowed);
        self::assertFalse($second->deferred);
        self::assertSame('rejected', $second->status());
    }

    public function testCanDeferLimitedOperations(): void
    {
        $limiter = new SearchOperationLimiter([
            'search.reindex.dispatch' => ['limit' => 1, 'window_seconds' => 60, 'mode' => 'defer'],
        ]);

        $first = $limiter->decide(new SearchOperationLimitRequest('search.reindex.dispatch', 'admin', 1));
        $second = $limiter->decide(new SearchOperationLimitRequest('search.reindex.dispatch', 'admin', 1));

        self::assertTrue($first->allowed);
        self::assertFalse($second->allowed);
        self::assertTrue($second->deferred);
        self::assertSame('deferred', $second->status());
    }

    public function testUnconfiguredOperationIsAllowedWithoutConsumingABucket(): void
    {
        $limiter = new SearchOperationLimiter();

        $decision = $limiter->decide(new SearchOperationLimitRequest('search.health', null, 3));

        self::assertTrue($decision->allowed);
        self::assertSame('allowed', $decision->status());
        self::assertSame([
            'limiter' => 'in_memory',
            'operation' => 'search.health',
            'identity' => null,
            'configured' => false,
        ], $decision->metadata);
    }

    public function testEmptyModeUsesDefaultAndLimitsIdentitiesIndependently(): void
    {
        $limiter = new SearchOperationLimiter([
            'search.query' => ['limit' => 0, 'window_seconds' => 0, 'mode' => ''],
        ], defaultMode: 'reject');

        $firstAnonymous = $limiter->decide(new SearchOperationLimitRequest('search.query', null, 1));
        $secondAnonymous = $limiter->decide(new SearchOperationLimitRequest('search.query', null, 1));
        $identified = $limiter->decide(new SearchOperationLimitRequest('search.query', 'user-2', 1));

        self::assertTrue($firstAnonymous->allowed);
        self::assertFalse($secondAnonymous->allowed);
        self::assertFalse($secondAnonymous->deferred);
        self::assertSame(1, $secondAnonymous->metadata['limit']);
        self::assertSame(1, $secondAnonymous->metadata['window_seconds']);
        self::assertSame(0, $secondAnonymous->metadata['remaining']);
        self::assertGreaterThanOrEqual(1, $secondAnonymous->retryAfterSeconds);
        self::assertTrue($identified->allowed);
    }
}
