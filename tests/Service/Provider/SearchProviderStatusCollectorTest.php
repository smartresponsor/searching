<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service\Provider;

use App\Searching\Provider\Backend\SearchNullProvider;
use App\Searching\Service\Provider\SearchProviderStatusCollector;
use App\Searching\Service\Registry\SearchProviderRegistry;
use PHPUnit\Framework\TestCase;

final class SearchProviderStatusCollectorTest extends TestCase
{
    public function testItCollectsRegisteredProviderStatuses(): void
    {
        $registry = new SearchProviderRegistry();
        $registry->add('null', new SearchNullProvider());

        $collector = new SearchProviderStatusCollector($registry);
        $statuses = $collector->collect();

        self::assertArrayHasKey('null', $statuses);
        self::assertSame('disabled', $statuses['null']->status);
    }
}
