<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service\Provider;

use App\Searching\Service\Provider\SearchUnavailableBackendClient;
use PHPUnit\Framework\TestCase;

final class SearchUnavailableBackendClientTest extends TestCase
{
    public function testItReportsUnavailableStatusWithoutThrowing(): void
    {
        $client = new SearchUnavailableBackendClient();
        $status = $client->getStatus('elasticsearch', [
            'enabled' => true,
            'dsn' => null,
            'index_prefix' => 'sr',
        ]);

        self::assertFalse($status->available);
        self::assertSame('unavailable', $status->status);
        self::assertSame('sr', $status->metadata['indexPrefix']);
    }

    public function testItReturnsEmptySearchResultInSafeMode(): void
    {
        $client = new SearchUnavailableBackendClient();
        $result = $client->search('sr_all', ['query' => 'demo']);

        self::assertSame(0, $result->total);
        self::assertSame('unavailable', $result->metadata['provider_mode']);
        self::assertSame('sr_all', $result->metadata['index']);
    }
}
