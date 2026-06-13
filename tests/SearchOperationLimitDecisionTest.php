<?php

declare(strict_types=1);

namespace App\Searching\Tests;

use App\Searching\Value\Flow\SearchOperationLimitDecision;
use PHPUnit\Framework\TestCase;

final class SearchOperationLimitDecisionTest extends TestCase
{
    public function testDecisionMetadataContainsStableStatus(): void
    {
        $decision = SearchOperationLimitDecision::defer('too_many_requests', 15, ['limit' => 5]);

        self::assertFalse($decision->allowed);
        self::assertTrue($decision->deferred);
        self::assertSame('deferred', $decision->status());
        self::assertSame(15, $decision->toMetadata()['retry_after_seconds']);
        self::assertSame(5, $decision->toMetadata()['limit']);
    }
}
