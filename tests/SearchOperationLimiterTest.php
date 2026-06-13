<?php

declare(strict_types=1);

namespace App\Searching\Tests;

use App\Searching\Service\Flow\SearchOperationLimiter;
use App\Searching\Value\Flow\SearchOperationLimitRequest;
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
}
