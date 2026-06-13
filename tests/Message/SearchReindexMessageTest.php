<?php

declare(strict_types=1);

namespace App\Searching\Tests\Message;

use App\Searching\Message\SearchReindexMessage;
use PHPUnit\Framework\TestCase;

final class SearchReindexMessageTest extends TestCase
{
    public function testItParsesChangedSinceDate(): void
    {
        $message = new SearchReindexMessage(
            jobKey: 'job-1',
            component: 'cataloging',
            resourceType: 'product',
            changedSince: '2026-05-27T10:00:00+00:00',
            requestedBy: 'admin',
        );

        self::assertSame('job-1', $message->jobKey);
        self::assertSame('cataloging', $message->component);
        self::assertSame('product', $message->resourceType);
        self::assertSame('admin', $message->requestedBy);
        self::assertSame($message->getDeduplicationKey(), $message->getDeduplicationKey());
        self::assertSame('2026-05-27T10:00:00+00:00', $message->getChangedSinceDate()?->format(\DateTimeInterface::ATOM));
    }

    public function testItAllowsEmptyChangedSince(): void
    {
        $message = new SearchReindexMessage(jobKey: 'job-2');

        self::assertNull($message->getChangedSinceDate());
    }
}
