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
use App\Searching\ValueObject\Provider\SearchProviderResult;
use App\Searching\ValueObject\Provider\SearchProviderStatus;
use App\Searching\ValueObject\Query\SearchQuery;
use App\Searching\ValueObject\Query\SearchSuggestionQuery;
use PHPUnit\Framework\TestCase;

final class SearchDocumentIndexerFreshnessTest extends TestCase
{
    public function testCurrentDocumentIsMarkedUnchangedAndNotSentToProvider(): void
    {
        $provider = new CountingSearchProvider();
        $tracker = new CurrentSearchIndexedResourceTracker();
        $indexer = new SearchDocumentIndexer(
            searchProvider: $provider,
            documentNormalizer: new SearchDocumentNormalizer(),
            fingerprintCalculator: new SearchDocumentFingerprintCalculator(),
            indexedResourceTracker: $tracker,
        );

        $indexer->index(self::document());

        self::assertSame(0, $provider->indexCount);
        self::assertSame(1, $tracker->unchangedCount);
        self::assertSame(0, $tracker->indexedCount);
    }

    public function testChangedDocumentIsSentToProviderAndMarkedIndexed(): void
    {
        $provider = new CountingSearchProvider();
        $tracker = new CurrentSearchIndexedResourceTracker(false);
        $indexer = new SearchDocumentIndexer(
            searchProvider: $provider,
            documentNormalizer: new SearchDocumentNormalizer(),
            fingerprintCalculator: new SearchDocumentFingerprintCalculator(),
            indexedResourceTracker: $tracker,
        );

        $indexer->index(self::document());

        self::assertSame(1, $provider->indexCount);
        self::assertSame(0, $tracker->unchangedCount);
        self::assertSame(1, $tracker->indexedCount);
    }

    private static function document(): SearchDocument
    {
        return new SearchDocument(
            component: 'cataloging',
            resourceType: 'product',
            resourceId: '42',
            title: 'Product 42',
            summary: null,
            body: null,
            keywords: [],
            facets: [],
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

final class CurrentSearchIndexedResourceTracker implements SearchIndexedResourceTrackerInterface
{
    public int $indexedCount = 0;
    public int $unchangedCount = 0;

    public function __construct(private readonly bool $current = true)
    {
    }

    public function find(string $component, string $resourceType, string $resourceId): ?SearchIndexedResourceState
    {
        unset($component, $resourceType, $resourceId);

        return null;
    }

    public function isCurrent(SearchDocumentFingerprint $fingerprint): bool
    {
        unset($fingerprint);

        return $this->current;
    }

    public function markIndexed(SearchDocumentFingerprint $fingerprint): SearchIndexedResourceState
    {
        ++$this->indexedCount;

        return $this->state($fingerprint, 'indexed');
    }

    public function markUnchanged(SearchDocumentFingerprint $fingerprint): SearchIndexedResourceState
    {
        ++$this->unchangedCount;

        return $this->state($fingerprint, 'unchanged');
    }

    public function markFailed(SearchDocumentFingerprint $fingerprint, string $errorMessage): SearchIndexedResourceState
    {
        return $this->state($fingerprint, 'failed', $errorMessage);
    }

    public function markRemoved(string $component, string $resourceType, string $resourceId): SearchIndexedResourceState
    {
        return new SearchIndexedResourceState($component, $resourceType, $resourceId, null, null, null, 'removed');
    }

    private function state(SearchDocumentFingerprint $fingerprint, string $status, ?string $errorMessage = null): SearchIndexedResourceState
    {
        return new SearchIndexedResourceState(
            component: $fingerprint->component,
            resourceType: $fingerprint->resourceType,
            resourceId: $fingerprint->resourceId,
            documentHash: $fingerprint->documentHash,
            indexedAt: new \DateTimeImmutable(),
            sourceUpdatedAt: $fingerprint->sourceUpdatedAt,
            status: $status,
            errorMessage: $errorMessage,
        );
    }
}

final class CountingSearchProvider implements SearchProviderInterface
{
    public int $indexCount = 0;

    public function index(SearchDocument $document): void
    {
        unset($document);
        ++$this->indexCount;
    }

    public function bulkIndex(iterable $documents): void
    {
        foreach ($documents as $_document) {
            ++$this->indexCount;
        }
    }

    public function delete(string $component, string $resourceType, string $resourceId): void
    {
        unset($component, $resourceType, $resourceId);
    }

    public function search(SearchQuery $query): SearchProviderResult
    {
        unset($query);

        return new SearchProviderResult(total: 0, items: []);
    }

    public function suggest(SearchSuggestionQuery $query): array
    {
        unset($query);

        return [];
    }

    public function getStatus(): SearchProviderStatus
    {
        return new SearchProviderStatus('counting', true, 'test');
    }
}
