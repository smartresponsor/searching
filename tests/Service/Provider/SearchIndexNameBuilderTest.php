<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service\Provider;

use App\Searching\Builder\Provider\SearchIndexNameBuilder;
use App\Searching\ValueObject\Document\SearchDocument;
use App\Searching\ValueObject\Query\SearchQuery;
use PHPUnit\Framework\TestCase;

final class SearchIndexNameBuilderTest extends TestCase
{
    public function testItBuildsStableDocumentIndexNamesAndIds(): void
    {
        $builder = new SearchIndexNameBuilder();
        $document = new SearchDocument(
            component: 'Cataloging',
            resourceType: 'Product Item',
            resourceId: 'ABC-123',
            title: 'Demo',
            summary: null,
            body: null,
            keywords: [],
            facets: [],
            permissions: [],
            locale: null,
            vendorId: null,
            ownerId: null,
            routeName: 'catalog_product_show',
            routeParameters: ['id' => 123],
            updatedAt: new \DateTimeImmutable('2026-01-01T00:00:00+00:00'),
        );

        self::assertSame('sr_cataloging_product_item', $builder->buildForDocument($document, 'sr'));
        self::assertSame('cataloging_product_item_abc_123', $builder->buildDocumentId($document));
        self::assertSame(
            $builder->buildDocumentId($document),
            $builder->buildDocumentIdForParts(' Cataloging ', 'Product Item', 'ABC-123'),
        );
    }

    public function testItBuildsQueryIndexNames(): void
    {
        $builder = new SearchIndexNameBuilder();

        self::assertSame('sr_cataloging_product', $builder->buildForQuery(new SearchQuery('phone', ['cataloging'], ['product']), 'sr'));
        self::assertSame('sr_cataloging_all', $builder->buildForQuery(new SearchQuery('phone', ['cataloging']), 'sr'));
        self::assertSame('sr_all', $builder->buildForQuery(new SearchQuery('phone'), 'sr'));
    }
}
