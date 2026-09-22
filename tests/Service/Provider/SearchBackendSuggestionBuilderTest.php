<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service\Provider;

use App\Searching\Builder\Provider\SearchBackendSuggestionBuilder;
use App\Searching\ValueObject\Provider\SearchProviderConfiguration;
use App\Searching\ValueObject\Query\SearchSuggestionQuery;
use PHPUnit\Framework\TestCase;

final class SearchBackendSuggestionBuilderTest extends TestCase
{
    public function testItBuildsSuggestionPayloadWithPrefixFuzzyAndFilters(): void
    {
        $builder = new SearchBackendSuggestionBuilder();
        $backendQuery = $builder->build(
            new SearchSuggestionQuery(
                query: 'iph',
                components: ['cataloging'],
                resourceTypes: ['product'],
                filters: ['facets.status' => 'active'],
                limit: 7,
                locale: 'en_US',
                vendorId: 'vendor-1',
                userId: 'user-1',
            ),
            new SearchProviderConfiguration(
                nameEntity: 'opensearch',
                enabled: true,
                dsn: null,
                indexPrefix: 'sr',
                options: ['suggestion_fields' => ['title^6', 'keywords^4']],
            ),
        );

        $payload = $backendQuery->toPayload();
        /** @var array{from: int, size: int, query: array{bool: array<string, mixed>}, _searching: array{suggestion: bool, components: list<string>, resource_types: list<string>, user_id: string}} $payload */
        self::assertSame(0, $payload['from']);
        self::assertSame(7, $payload['size']);
        self::assertTrue($payload['_searching']['suggestion']);
        self::assertSame(['cataloging'], $payload['_searching']['components']);
        self::assertSame(['product'], $payload['_searching']['resource_types']);
        self::assertSame('user-1', $payload['_searching']['user_id']);
        self::assertArrayHasKey('bool', $payload['query']);
    }
}
