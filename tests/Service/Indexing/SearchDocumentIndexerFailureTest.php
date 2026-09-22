<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service\Indexing;

use App\Searching\Contract\Indexing\SearchIndexedResourceTrackerInterface;
use App\Searching\Contract\Provider\SearchProviderInterface;
use App\Searching\Normalizer\SearchDocumentNormalizer;
use App\Searching\Service\Indexing\SearchDocumentFingerprintCalculator;
use App\Searching\Service\Indexing\SearchDocumentIndexer;
use App\Searching\ValueObject\Document\SearchDocument;
use App\Searching\ValueObject\Indexing\SearchDocumentFingerprint;
use App\Searching\ValueObject\Indexing\SearchIndexedResourceState;
use PHPUnit\Framework\TestCase;

final class SearchDocumentIndexerFailureTest extends TestCase
{
    public function testBulkIndexSkipsCurrentAndIndexesChangedDocuments(): void
    {
        $provider = $this->createMock(SearchProviderInterface::class);
        $provider->expects(self::once())
            ->method('bulkIndex')
            ->with(self::callback(static fn (iterable $documents): bool => 1 === count(iterator_to_array($documents))));

        $tracker = $this->createMock(SearchIndexedResourceTrackerInterface::class);
        $tracker->expects(self::exactly(2))
            ->method('isCurrent')
            ->willReturnOnConsecutiveCalls(true, false);
        $tracker->expects(self::once())
            ->method('markUnchanged')
            ->willReturnCallback(static fn (SearchDocumentFingerprint $fingerprint): SearchIndexedResourceState => self::state($fingerprint, 'unchanged'));
        $tracker->expects(self::once())
            ->method('markIndexed')
            ->willReturnCallback(static fn (SearchDocumentFingerprint $fingerprint): SearchIndexedResourceState => self::state($fingerprint, 'indexed'));
        $tracker->expects(self::never())->method('markFailed');

        self::indexer($provider, $tracker)->bulkIndex([self::document('41'), self::document('42')]);
    }

    public function testSingleIndexFailureIsTrackedAndRethrown(): void
    {
        $provider = $this->createMock(SearchProviderInterface::class);
        $provider->method('index')->willThrowException(new \RuntimeException('provider failure'));

        $tracker = $this->createMock(SearchIndexedResourceTrackerInterface::class);
        $tracker->method('isCurrent')->willReturn(false);
        $tracker->expects(self::once())
            ->method('markFailed')
            ->with(self::isInstanceOf(SearchDocumentFingerprint::class), 'provider failure')
            ->willReturnCallback(static fn (SearchDocumentFingerprint $fingerprint): SearchIndexedResourceState => self::state($fingerprint, 'failed', 'provider failure'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('provider failure');

        self::indexer($provider, $tracker)->index(self::document());
    }

    public function testBulkFailureMarksEveryPendingDocumentFailed(): void
    {
        $provider = $this->createMock(SearchProviderInterface::class);
        $provider->method('bulkIndex')->willThrowException(new \RuntimeException('bulk provider failure'));

        $tracker = $this->createMock(SearchIndexedResourceTrackerInterface::class);
        $tracker->method('isCurrent')->willReturn(false);
        $tracker->expects(self::exactly(2))
            ->method('markFailed')
            ->with(self::isInstanceOf(SearchDocumentFingerprint::class), 'bulk provider failure')
            ->willReturnCallback(static fn (SearchDocumentFingerprint $fingerprint): SearchIndexedResourceState => self::state($fingerprint, 'failed', 'bulk provider failure'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('bulk provider failure');

        self::indexer($provider, $tracker)->bulkIndex([self::document('41'), self::document('42')]);
    }

    private static function indexer(SearchProviderInterface $provider, SearchIndexedResourceTrackerInterface $tracker): SearchDocumentIndexer
    {
        return new SearchDocumentIndexer(
            searchProvider: $provider,
            documentNormalizer: new SearchDocumentNormalizer(),
            fingerprintCalculator: new SearchDocumentFingerprintCalculator(),
            indexedResourceTracker: $tracker,
        );
    }

    private static function document(string $resourceId = '42'): SearchDocument
    {
        return new SearchDocument(
            component: 'cataloging',
            resourceType: 'product',
            resourceId: $resourceId,
            title: 'Product '.$resourceId,
            summary: null,
            body: null,
            keywords: [],
            facets: [],
            permissions: [],
            locale: 'en',
            vendorId: null,
            ownerId: null,
            routeName: 'catalog_product_show',
            routeParameters: ['id' => $resourceId],
            updatedAt: new \DateTimeImmutable('2026-05-27T00:00:00+00:00'),
        );
    }

    private static function state(SearchDocumentFingerprint $fingerprint, string $status, ?string $error = null): SearchIndexedResourceState
    {
        return new SearchIndexedResourceState(
            component: $fingerprint->component,
            resourceType: $fingerprint->resourceType,
            resourceId: $fingerprint->resourceId,
            documentHash: $fingerprint->documentHash,
            indexedAt: new \DateTimeImmutable(),
            sourceUpdatedAt: $fingerprint->sourceUpdatedAt,
            status: $status,
            errorMessage: $error,
        );
    }
}
