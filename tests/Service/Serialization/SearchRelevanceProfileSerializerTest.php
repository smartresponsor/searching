<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service\Serialization;

use App\Searching\Entity\SearchRelevanceProfileEntity;
use App\Searching\Service\Serialization\SearchRelevanceProfileSerializer;
use PHPUnit\Framework\TestCase;

final class SearchRelevanceProfileSerializerTest extends TestCase
{
    public function testSerializeProfile(): void
    {
        $profile = SearchRelevanceProfileEntity::create('Catalog Default', ['title' => 3, 'summary' => 1.5], 'cataloging', 'product');
        $serializer = new SearchRelevanceProfileSerializer();

        $serialized = $serializer->serialize($profile);

        self::assertSame('Catalog Default', $serialized['nameEntity']);
        self::assertSame('cataloging', $serialized['component']);
        self::assertSame('product', $serialized['resourceType']);
        self::assertSame(['title' => 3, 'summary' => 1.5], $serialized['fieldWeights']);
        self::assertTrue($serialized['enabled']);
    }
}
