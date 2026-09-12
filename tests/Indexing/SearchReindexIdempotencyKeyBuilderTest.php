<?php

declare(strict_types=1);

namespace App\Searching\Tests\Indexing;

use App\Searching\Builder\Indexing\SearchReindexIdempotencyKeyBuilder;
use PHPUnit\Framework\TestCase;

final class SearchReindexIdempotencyKeyBuilderTest extends TestCase
{
    public function testItBuildsStableKeysForEquivalentRequests(): void
    {
        $builder = new SearchReindexIdempotencyKeyBuilder();
        $changedSince = new \DateTimeImmutable('2026-05-27T10:00:00+00:00');

        self::assertSame(
            $builder->build('Cataloging', 'Product', $changedSince),
            $builder->build(' cataloging ', ' product ', $changedSince),
        );
    }

    public function testDifferentChangedSinceProducesDifferentKey(): void
    {
        $builder = new SearchReindexIdempotencyKeyBuilder();

        self::assertNotSame(
            $builder->build('cataloging', 'product', new \DateTimeImmutable('2026-05-27T10:00:00+00:00')),
            $builder->build('cataloging', 'product', new \DateTimeImmutable('2026-05-28T10:00:00+00:00')),
        );
    }
}
