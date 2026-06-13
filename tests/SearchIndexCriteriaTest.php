<?php

declare(strict_types=1);

namespace App\Searching\Tests;

use App\Searching\Value\Indexing\SearchIndexCriteria;
use PHPUnit\Framework\TestCase;

final class SearchIndexCriteriaTest extends TestCase
{
    public function testCriteriaAcceptsQueryAliases(): void
    {
        $criteria = SearchIndexCriteria::fromArray([
            'provider' => 'opensearch',
            'component' => 'cataloging',
            'resource' => 'product',
            'enabled' => 'false',
            'limit' => '999',
            'offset' => '3',
        ]);

        self::assertSame('opensearch', $criteria->provider);
        self::assertSame('cataloging', $criteria->component);
        self::assertSame('product', $criteria->resourceType);
        self::assertFalse($criteria->enabled);
        self::assertSame(500, $criteria->limit);
        self::assertSame(3, $criteria->offset);
    }
}
