<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service\Serialization;

use App\Searching\Entity\SearchIndexedResourceEntity;
use App\Searching\Service\Serialization\SearchIndexedResourceSerializer;
use PHPUnit\Framework\TestCase;

final class SearchIndexedResourceSerializerTest extends TestCase
{
    public function testItSerializesIndexedResourceLedgerEntry(): void
    {
        $resource = new SearchIndexedResourceEntity('cataloging', 'product', '42');
        $resource->markIndexed('abc123', new \DateTimeImmutable('2026-05-01T10:00:00+00:00'));

        $payload = (new SearchIndexedResourceSerializer())->serialize($resource);

        self::assertSame('cataloging', $payload['component']);
        self::assertSame('product', $payload['resourceType']);
        self::assertSame('42', $payload['resourceId']);
        self::assertSame('abc123', $payload['documentHash']);
        self::assertSame('indexed', $payload['status']);
        self::assertFalse($payload['stale']);
    }

    public function testItMarksPendingResourceAsStale(): void
    {
        $resource = new SearchIndexedResourceEntity('messaging', 'message', '99');

        $payload = (new SearchIndexedResourceSerializer())->serialize($resource);

        self::assertSame('pending', $payload['status']);
        self::assertTrue($payload['stale']);
    }

    public function testItSerializesListAndRemovedResourceIsNotStale(): void
    {
        $removed = new SearchIndexedResourceEntity('ordering', 'order', '7');
        $removed->markRemoved();

        $payload = (new SearchIndexedResourceSerializer())->serializeList([$removed]);

        self::assertCount(1, $payload);
        self::assertSame('removed', $payload[0]['status']);
        self::assertFalse($payload[0]['stale']);
    }

    public function testItMarksResourceStaleWhenSourceIsNewerThanIndexedProjection(): void
    {
        $resource = new SearchIndexedResourceEntity('ordering', 'order', '8');
        $resource->markIndexed('hash-8', new \DateTimeImmutable('2099-09-15T11:00:00+00:00'));

        $payload = (new SearchIndexedResourceSerializer())->serialize($resource);

        self::assertTrue($payload['stale']);
        self::assertSame('indexed', $payload['status']);
    }
}
