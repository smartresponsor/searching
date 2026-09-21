<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service\Provider;

use App\Searching\Builder\Provider\SearchBulkOperationBuilder;
use App\Searching\Builder\Provider\SearchIndexNameBuilder;
use App\Searching\Service\Provider\SearchDocumentPayloadMapper;
use App\Searching\Value\Document\SearchDocument;
use PHPUnit\Framework\TestCase;

final class SearchBulkOperationBuilderTest extends TestCase
{
    public function testItBuildsGroupedBulkIndexOperations(): void
    {
        $builder = new SearchBulkOperationBuilder(new SearchIndexNameBuilder(), new SearchDocumentPayloadMapper());
        $documents = [
            $this->document('cataloging', 'product', '1'),
            $this->document('cataloging', 'product', '2'),
            $this->document('messaging', 'message', '3'),
        ];

        $set = $builder->buildIndexOperations($documents, 'sr');
        $groups = $set->groupedByIndex();

        self::assertCount(3, $set);
        self::assertCount(2, $groups['sr_cataloging_product']);
        self::assertCount(1, $groups['sr_messaging_message']);
    }

    private function document(string $component, string $resourceType, string $resourceId): SearchDocument
    {
        return new SearchDocument(
            component: $component,
            resourceType: $resourceType,
            resourceId: $resourceId,
            title: 'Demo',
            summary: null,
            body: null,
            keywords: [],
            facets: [],
            permissions: [],
            locale: null,
            vendorId: null,
            ownerId: null,
            routeName: 'demo_show',
            routeParameters: ['id' => $resourceId],
            updatedAt: new \DateTimeImmutable('2026-01-01T00:00:00+00:00'),
        );
    }
}
