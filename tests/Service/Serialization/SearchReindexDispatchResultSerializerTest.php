<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service\Serialization;

use App\Searching\Service\Serialization\SearchReindexDispatchResultSerializer;
use App\Searching\Value\Indexing\SearchReindexDispatchResult;
use App\Searching\Value\Indexing\SearchReindexResult;
use PHPUnit\Framework\TestCase;

final class SearchReindexDispatchResultSerializerTest extends TestCase
{
    public function testItSerializesQueuedDispatchResult(): void
    {
        $serializer = new SearchReindexDispatchResultSerializer();
        $payload = $serializer->serialize(new SearchReindexDispatchResult(
            jobId: 'job-1',
            mode: 'messenger',
            queued: true,
            metadata: ['requested_by' => 'admin'],
        ));

        self::assertSame('job-1', $payload['jobId']);
        self::assertSame('messenger', $payload['mode']);
        self::assertTrue($payload['queued']);
        self::assertNull($payload['syncResult']);
        self::assertSame(['requested_by' => 'admin'], $payload['metadata']);
    }

    public function testItSerializesSyncDispatchResult(): void
    {
        $serializer = new SearchReindexDispatchResultSerializer();
        $payload = $serializer->serialize(new SearchReindexDispatchResult(
            jobId: 'job-2',
            mode: 'sync',
            queued: false,
            syncResult: new SearchReindexResult('job-2', 1, 10, 0),
        ));

        self::assertSame('job-2', $payload['jobId']);
        self::assertFalse($payload['queued']);
        self::assertSame(10, $payload['syncResult']['documentCount']);
    }
}
