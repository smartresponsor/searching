<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service\Provider;

use App\Searching\Provider\Backend\SearchNullProvider;
use App\Searching\Value\Query\SearchQuery;
use PHPUnit\Framework\TestCase;

final class SearchNullProviderTest extends TestCase
{
    public function testSearchReturnsEmptyResult(): void
    {
        $provider = new SearchNullProvider();
        $result = $provider->search(new SearchQuery('anything'));

        self::assertSame(0, $result->total);
        self::assertSame([], $result->items);
    }
}
