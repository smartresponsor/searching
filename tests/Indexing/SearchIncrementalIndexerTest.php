<?php

declare(strict_types=1);

namespace App\Searching\Tests\Indexing;

use App\Searching\Contract\Indexing\SearchDocumentIndexerInterface;
use App\Searching\Contract\Provider\SearchProviderInterface;
use App\Searching\Service\Indexing\SearchIncrementalIndexer;
use App\Searching\ValueObject\Document\SearchDocument;
use App\Searching\ValueObject\Provider\SearchProviderResult;
use App\Searching\ValueObject\Provider\SearchProviderStatus;
use App\Searching\ValueObject\Query\SearchQuery;
use App\Searching\ValueObject\Query\SearchSuggestionQuery;
use PHPUnit\Framework\TestCase;

final class SearchIncrementalIndexerTest extends TestCase
{
    public function testIndexChangedDocumentDelegatesToDocumentIndexer(): void
    {
        $document = self::document();
        $documentIndexer = new RecordingDocumentIndexer();
        $provider = new RecordingSearchProvider();
        $indexer = new SearchIncrementalIndexer($documentIndexer, $provider);

        $result = $indexer->indexChangedDocument($document, 'updated');

        self::assertSame('indexed', $result->status);
        self::assertSame('cataloging', $result->component);
        self::assertSame('product', $result->resourceType);
        self::assertSame('42', $result->resourceId);
        self::assertSame($document, $documentIndexer->indexedDocument);
        self::assertSame([], $provider->deleted);
    }

    public function testRemoveResourceDelegatesToSearchProviderDelete(): void
    {
        $documentIndexer = new RecordingDocumentIndexer();
        $provider = new RecordingSearchProvider();
        $indexer = new SearchIncrementalIndexer($documentIndexer, $provider);

        $result = $indexer->removeResource('messaging', 'message', 'abc', 'deleted');

        self::assertSame('removed', $result->status);
        self::assertSame(['messaging', 'message', 'abc'], $provider->deleted);
        self::assertNull($documentIndexer->indexedDocument);
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

final class RecordingDocumentIndexer implements SearchDocumentIndexerInterface
{
    public ?SearchDocument $indexedDocument = null;

    public function index(SearchDocument $document): void
    {
        $this->indexedDocument = $document;
    }

    public function bulkIndex(iterable $documents): void
    {
        foreach ($documents as $document) {
            $this->indexedDocument = $document;
        }
    }
}

final class RecordingSearchProvider implements SearchProviderInterface
{
    /** @var list<string> */
    public array $deleted = [];

    public function index(SearchDocument $document): void
    {
    }

    public function bulkIndex(iterable $documents): void
    {
        foreach ($documents as $_document) {
        }
    }

    public function delete(string $component, string $resourceType, string $resourceId): void
    {
        $this->deleted = [$component, $resourceType, $resourceId];
    }

    public function search(SearchQuery $query): SearchProviderResult
    {
        return new SearchProviderResult(total: 0, items: []);
    }

    public function suggest(SearchSuggestionQuery $query): array
    {
        return [];
    }

    public function getStatus(): SearchProviderStatus
    {
        return new SearchProviderStatus('recording', true, 'test');
    }
}
