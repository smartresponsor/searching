<?php

declare(strict_types=1);

namespace App\Searching\Tests;

use App\Searching\Entity\SearchIndexEntity;
use App\Searching\Service\Serialization\SearchIndexSerializer;
use PHPUnit\Framework\TestCase;

final class SearchIndexSerializerTest extends TestCase
{
    public function testSerializeIndexDefinition(): void
    {
        $index = new SearchIndexEntity('Catalog products', 'opensearch', 'sr_cataloging_product', 'cataloging', 'product');
        $index->markIndexed(new \DateTimeImmutable('2026-05-27T00:00:00+00:00'));

        $payload = (new SearchIndexSerializer())->serialize($index);

        self::assertSame('Catalog products', $payload['nameEntity']);
        self::assertSame('opensearch', $payload['provider']);
        self::assertSame('sr_cataloging_product', $payload['indexName']);
        self::assertSame('cataloging', $payload['component']);
        self::assertSame('product', $payload['resourceType']);
        self::assertTrue($payload['enabled']);
        self::assertSame('2026-05-27T00:00:00+00:00', $payload['lastIndexedAt']);
    }
}
