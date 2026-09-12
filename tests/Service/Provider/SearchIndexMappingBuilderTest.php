<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service\Provider;

use App\Searching\Builder\Provider\SearchIndexMappingBuilder;
use App\Searching\Value\Provider\SearchProviderConfiguration;
use PHPUnit\Framework\TestCase;

final class SearchIndexMappingBuilderTest extends TestCase
{
    public function testItBuildsBackendNeutralMapping(): void
    {
        $builder = new SearchIndexMappingBuilder();
        $mapping = $builder->build(
            indexName: 'sr_cataloging_product',
            component: 'cataloging',
            resourceType: 'product',
            configuration: new SearchProviderConfiguration(
                nameEntity: 'opensearch',
                enabled: true,
                dsn: 'http://localhost:9200',
                indexPrefix: 'sr',
                options: ['text_analyzer' => 'standard'],
            ),
        );

        $payload = $mapping->toPayload();
        /** @var array{mappings: array{_meta: array{component: string}, properties: array{title: array<string, mixed>, updated_at: array{type: string}}}} $payload */
        self::assertSame('sr_cataloging_product', $mapping->indexName);
        self::assertSame('cataloging', $payload['mappings']['_meta']['component']);
        self::assertArrayHasKey('title', $payload['mappings']['properties']);
        self::assertSame('date', $payload['mappings']['properties']['updated_at']['type']);
    }
}
