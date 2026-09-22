<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service\Provider;

use App\Searching\Builder\Provider\SearchBackendQueryBuilder;
use App\Searching\ValueObject\Provider\SearchProviderConfiguration;
use App\Searching\ValueObject\Query\SearchQuery;
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
                vendorId: 'vendor-1',
                userId: 'user-1',
            ),
            new SearchProviderConfiguration(
                nameEntity: 'elasticsearch',
                enabled: true,
                dsn: null,
                indexPrefix: 'sr',
                options: ['facet_fields' => ['facets.status']],
            ),
        );

        $payload = $backendQuery->toPayload();
        /** @var array{from: int, size: int, highlight: array<string, mixed>, aggs: array<string, mixed>, sort: array<string, string>, _searching: array{components: list<string>}} $payload */
        self::assertSame(10, $payload['from']);
        self::assertSame(10, $payload['size']);
        self::assertArrayHasKey('highlight', $payload);
        self::assertArrayHasKey('aggs', $payload);
        self::assertSame(['updated_at' => 'desc'], $payload['sort']);
        self::assertSame(['billing'], $payload['_searching']['components']);
    }

    public function testItBuildsAnyAllAndRangeFacetFilters(): void
    {
        $payload = (new SearchBackendQueryBuilder())->build(
            new SearchQuery(
                query: '',
                filters: [
                    'facets.brand' => ['operator' => 'any', 'values' => ['acme', 'globex']],
                    'facets.tag' => ['operator' => 'all', 'values' => ['sale', 'featured']],
                    'price' => ['range' => ['min' => 10, 'max' => 100, 'includeMin' => true, 'includeMax' => false]],
                ],
            ),
            new SearchProviderConfiguration(
                nameEntity: 'elasticsearch',
                enabled: true,
                dsn: null,
                indexPrefix: 'sr',
            ),
        )->toPayload();

        /** @var array{query: array{bool: array{filter: list<array<string, mixed>>}}} $payload */
        self::assertSame(
            ['terms' => ['facets.brand' => ['acme', 'globex']]],
            $payload['query']['bool']['filter'][0],
        );
        self::assertSame(
            ['bool' => ['must' => [
                ['term' => ['facets.tag' => 'sale']],
                ['term' => ['facets.tag' => 'featured']],
            ]]],
            $payload['query']['bool']['filter'][1],
        );
        self::assertSame(
            ['range' => ['price' => ['gte' => 10, 'lt' => 100]]],
            $payload['query']['bool']['filter'][2],
        );
    }

    public function testItRejectsUnsupportedAssociativeFilterShape(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        (new SearchBackendQueryBuilder())->build(
            new SearchQuery(query: '', filters: ['facets.brand' => ['unexpected' => true]]),
            new SearchProviderConfiguration(
                nameEntity: 'elasticsearch',
                enabled: true,
                dsn: null,
                indexPrefix: 'sr',
            ),
        );
    }
}
