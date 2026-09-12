<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service\Serialization;

use App\Searching\Service\Serialization\SearchRegistrySerializer;
use App\Searching\Value\Registry\SearchableResourceDefinition;
use PHPUnit\Framework\TestCase;

final class SearchRegistrySerializerTest extends TestCase
{
    public function testItSerializesDefinitions(): void
    {
        $serializer = new SearchRegistrySerializer();
        $payload = $serializer->serializeDefinitions([
            new SearchableResourceDefinition('cataloging', 'product', 'ProductSearchDocumentProvider'),
        ]);
        /** @var array{total: int, resources: list<array{key: string}>} $payload */
        self::assertSame(1, $payload['total']);
        self::assertSame('cataloging:product', $payload['resources'][0]['key']);
    }
}
