<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service\Indexing;

use App\Searching\Service\Indexing\SearchNullReindexJobTracker;
use PHPUnit\Framework\TestCase;

final class SearchNullReindexJobTrackerTest extends TestCase
{
    public function testItCreatesNonPersistedJob(): void
    {
        $job = (new SearchNullReindexJobTracker())->request('cataloging', 'product');

        self::assertNotSame('', $job->getJobKey());
        self::assertSame('cataloging', $job->getComponent());
        self::assertSame('product', $job->getResourceType());
        self::assertSame('requested', $job->getStatus());
    }
}
