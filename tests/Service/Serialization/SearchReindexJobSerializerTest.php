<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service\Serialization;

use App\Searching\Entity\SearchReindexJobEntity;
use App\Searching\Service\Serialization\SearchReindexJobSerializer;
use PHPUnit\Framework\TestCase;

final class SearchReindexJobSerializerTest extends TestCase
{
    public function testItSerializesCompletedJob(): void
    {
        $job = new SearchReindexJobEntity('job-1', 'cataloging', 'product');
        $job->markRunning(1);
        $job->markCompleted(1, 12, 0);

        $payload = (new SearchReindexJobSerializer())->serialize($job);

        self::assertSame('job-1', $payload['jobKey']);
        self::assertSame('completed', $payload['status']);
        self::assertSame(12, $payload['processedCount']);
        self::assertSame(12, $payload['documentCount']);
        self::assertSame(0, $payload['failedCount']);
    }
}
