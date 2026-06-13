<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service\Indexing;

use App\Searching\Service\Indexing\SearchDocumentFingerprintCalculator;
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
            tenantId: null,
            ownerId: null,
            routeName: 'catalog_product_show',
            routeParameters: ['id' => 42],
            updatedAt: new \DateTimeImmutable('2026-05-27T00:00:00+00:00'),
        );
    }
}
