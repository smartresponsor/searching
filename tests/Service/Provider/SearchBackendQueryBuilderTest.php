<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service\Provider;

use App\Searching\Service\Provider\SearchBackendQueryBuilder;
use App\Searching\Value\Provider\SearchProviderConfiguration;
use App\Searching\Value\Query\SearchQuery;
use PHPUnit\Framework\TestCase;

final class SearchBackendQueryBuilderTest extends TestCase
{
    public function testItBuildsQueryPayloadWithFiltersHighlightsAndFacets(): void
    {
        $builder = new SearchBackendQueryBuilder();
        $backendQuery = $builder->build(
            new SearchQuery(
                query: 'invoice overdue',
                components: ['billing'],
                resourceTypes: ['invoice'],
                filters: ['facets.status' => 'open'],
                sort: ['updated_at' => 'desc'],
                page: 2,
                limit: 10,
                locale: 'en_US',
                tenantId: 'tenant-1',
                userId: 'user-1',
            ),
            new SearchProviderConfiguration(
                name: 'elasticsearch',
                enabled: true,
                dsn: null,
                indexPrefix: 'sr',
                options: ['facet_fields' => ['facets.status']],
            ),
        );

        $payload = $backendQuery->toPayload();

        self::assertSame(10, $payload['from']);
        self::assertSame(10, $payload['size']);
        self::assertArrayHasKey('highlight', $payload);
        self::assertArrayHasKey('aggs', $payload);
        self::assertSame(['updated_at' => 'desc'], $payload['sort']);
        self::assertSame(['billing'], $payload['_searching']['components']);
    }
}
