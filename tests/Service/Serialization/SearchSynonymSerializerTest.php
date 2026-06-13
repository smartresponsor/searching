<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service\Serialization;

use App\Searching\Entity\SearchSynonymEntity;
use App\Searching\Service\Serialization\SearchSynonymSerializer;
use PHPUnit\Framework\TestCase;

final class SearchSynonymSerializerTest extends TestCase
{
    public function testSerializeSynonym(): void
    {
        $synonym = SearchSynonymEntity::create('phone', ['mobile', 'cell'], 'en_US');
        $serializer = new SearchSynonymSerializer();

        $serialized = $serializer->serialize($synonym);

        self::assertSame('phone', $serialized['sourceTerm']);
        self::assertSame(['mobile', 'cell'], $serialized['targetTerms']);
        self::assertSame('en_US', $serialized['locale']);
        self::assertTrue($serialized['enabled']);
    }
}
