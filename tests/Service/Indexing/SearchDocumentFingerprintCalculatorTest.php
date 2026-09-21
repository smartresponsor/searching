<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service\Indexing;

use App\Searching\Service\Indexing\SearchDocumentFingerprintCalculator;
use App\Searching\Service\Indexing\SearchDocumentNormalizer;
use App\Searching\Value\Document\SearchDocument;
use PHPUnit\Framework\TestCase;

final class SearchDocumentFingerprintCalculatorTest extends TestCase
{
    public function testFingerprintIsStableForEquivalentAssociativePayloads(): void
    {
        $calculator = new SearchDocumentFingerprintCalculator();

        $first = $calculator->fingerprint(self::document(['brand' => 'Acme', 'category' => 'tools']));
        $second = $calculator->fingerprint(self::document(['category' => 'tools', 'brand' => 'Acme']));

        self::assertSame($first->documentHash, $second->documentHash);
        self::assertSame('cataloging', $first->component);
        self::assertSame('product', $first->resourceType);
        self::assertSame('42', $first->resourceId);
    }

    public function testNormalizerCleansTextDeduplicatesListsAndPreservesNulls(): void
    {
        $normalizer = new SearchDocumentNormalizer();
        $normalized = $normalizer->normalize(new SearchDocument(
            component: ' Ordering ', resourceType: ' Order ', resourceId: ' 42 ', title: ' Invoice 42 ',
            summary: ' Paid invoice ', body: ' Full body ', keywords: ['invoice', 'invoice', '42'],
            facets: ['status' => 'paid'], permissions: ['order.view', 'order.view', 'order.export'],
            locale: 'en', vendorId: 'vendor-1', ownerId: 'vendor-1', routeName: ' order_show ',
            routeParameters: ['id' => 42], updatedAt: new \DateTimeImmutable('2026-09-15T12:00:00+00:00'),
        ));

        self::assertSame('ordering', $normalized->component);
        self::assertSame('order', $normalized->resourceType);
        self::assertSame('42', $normalized->resourceId);
        self::assertSame('Invoice 42', $normalized->title);
        self::assertSame('Paid invoice', $normalized->summary);
        self::assertSame('Full body', $normalized->body);
        self::assertSame(['invoice', '42'], $normalized->keywords);
        self::assertSame(['order.view', 'order.export'], $normalized->permissions);
        self::assertSame('order_show', $normalized->routeName);

        $nullable = $normalizer->normalize(new SearchDocument(
            component: 'cataloging', resourceType: 'product', resourceId: '1', title: 'Product',
            summary: null, body: null, keywords: [], facets: [], permissions: [], locale: null,
            vendorId: null, ownerId: null, routeName: 'product_show', routeParameters: [],
            updatedAt: new \DateTimeImmutable('2026-09-15T12:00:00+00:00'),
        ));
        self::assertNull($nullable->summary);
        self::assertNull($nullable->body);
    }

    public function testFingerprintIsStableForEquivalentNestedAssociativePayloads(): void
    {
        $calculator = new SearchDocumentFingerprintCalculator();
        $first = self::document(['spec' => ['size' => 'L', 'color' => 'red'], 'tags' => ['one', 'two']]);
        $second = self::document(['tags' => ['one', 'two'], 'spec' => ['color' => 'red', 'size' => 'L']]);

        self::assertSame($calculator->fingerprint($first)->documentHash, $calculator->fingerprint($second)->documentHash);
    }

    /**
     * @param array<string, mixed> $facets
     */
    private static function document(array $facets): SearchDocument
    {
        return new SearchDocument(
            component: 'cataloging',
            resourceType: 'product',
            resourceId: '42',
            title: 'Product 42',
            summary: 'Summary',
            body: 'Body',
            keywords: ['one', 'two'],
            facets: $facets,
            permissions: [],
            locale: 'en',
            vendorId: null,
            ownerId: null,
            routeName: 'catalog_product_show',
            routeParameters: ['id' => 42],
            updatedAt: new \DateTimeImmutable('2026-05-27T00:00:00+00:00'),
        );
    }
}
