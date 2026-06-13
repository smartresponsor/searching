<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service\Provider;

use App\Searching\Service\Provider\SearchBackendSuggestionBuilder;
use App\Searching\Value\Provider\SearchProviderConfiguration;
use App\Searching\Value\Query\SearchSuggestionQuery;
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
                tenantId: 'tenant-1',
                userId: 'user-1',
            ),
            new SearchProviderConfiguration(
                name: 'opensearch',
                enabled: true,
                dsn: null,
                indexPrefix: 'sr',
                options: ['suggestion_fields' => ['title^6', 'keywords^4']],
            ),
        );

        $payload = $backendQuery->toPayload();

        self::assertSame(0, $payload['from']);
        self::assertSame(7, $payload['size']);
        self::assertTrue($payload['_searching']['suggestion']);
        self::assertSame(['cataloging'], $payload['_searching']['components']);
        self::assertSame(['product'], $payload['_searching']['resource_types']);
        self::assertSame('user-1', $payload['_searching']['user_id']);
        self::assertArrayHasKey('bool', $payload['query']);
    }
}
