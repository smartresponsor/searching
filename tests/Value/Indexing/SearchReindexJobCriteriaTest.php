<?php

declare(strict_types=1);

namespace App\Searching\Tests\Value\Indexing;

use App\Searching\Value\Indexing\SearchReindexJobCriteria;
use PHPUnit\Framework\TestCase;

final class SearchReindexJobCriteriaTest extends TestCase
{
    public function testItBuildsCriteriaFromArray(): void
    {
        $criteria = SearchReindexJobCriteria::fromArray([
            'job_id' => 'abc',
            'component' => 'cataloging',
            'resource' => 'product',
            'status' => 'completed',
            'limit' => '10',
            'offset' => '5',
        ]);

        self::assertSame('abc', $criteria->jobKey);
        self::assertSame('cataloging', $criteria->component);
        self::assertSame('product', $criteria->resourceType);
        self::assertSame('completed', $criteria->status);
        self::assertSame(10, $criteria->limit);
        self::assertSame(5, $criteria->offset);
    }
}
