<?php

declare(strict_types=1);

namespace App\Searching\Tests\Indexing;

use App\Searching\Contract\Indexing\SearchIncrementalIndexerInterface;
use App\Searching\Event\SearchDocumentChangedEvent;
use App\Searching\Event\SearchDocumentRemovedEvent;
use App\Searching\Subscriber\SearchDocumentChangeSubscriber;
use App\Searching\Value\Document\SearchDocument;
use App\Searching\Value\Indexing\SearchDocumentChangeResult;
use PHPUnit\Framework\TestCase;

final class SearchDocumentChangeSubscriberTest extends TestCase
{
    public function testSubscribedEventsExposeDocumentChangeEvents(): void
    {
        $events = SearchDocumentChangeSubscriber::getSubscribedEvents();

        self::assertSame('onDocumentChanged', $events[SearchDocumentChangedEvent::class]);
        self::assertSame('onDocumentRemoved', $events[SearchDocumentRemovedEvent::class]);
    }

    public function testSubscriberDelegatesChangedAndRemovedEvents(): void
    {
        $indexer = new RecordingIncrementalIndexer();
        $subscriber = new SearchDocumentChangeSubscriber($indexer);
        $document = self::document();

        $subscriber->onDocumentChanged(new SearchDocumentChangedEvent($document, 'updated'));
        $subscriber->onDocumentRemoved(new SearchDocumentRemovedEvent('messaging', 'message', 'abc', 'deleted'));

        self::assertSame($document, $indexer->indexedDocument);
        self::assertSame('updated', $indexer->indexReason);
        self::assertSame(['messaging', 'message', 'abc', 'deleted'], $indexer->removedResource);
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
            tenantId: null,
            ownerId: null,
            routeName: 'catalog_product_show',
            routeParameters: ['id' => 42],
            updatedAt: new \DateTimeImmutable('2026-05-27T00:00:00+00:00'),
        );
    }
}

final class RecordingIncrementalIndexer implements SearchIncrementalIndexerInterface
{
    public ?SearchDocument $indexedDocument = null;
    public ?string $indexReason = null;

    /** @var list<string>|null */
    public ?array $removedResource = null;

    public function indexChangedDocument(SearchDocument $document, string $changeReason = 'changed'): SearchDocumentChangeResult
    {
        $this->indexedDocument = $document;
        $this->indexReason = $changeReason;

        return SearchDocumentChangeResult::indexed($document->component, $document->resourceType, $document->resourceId);
    }

    public function removeResource(string $component, string $resourceType, string $resourceId, string $changeReason = 'removed'): SearchDocumentChangeResult
    {
        $this->removedResource = [$component, $resourceType, $resourceId, $changeReason];

        return SearchDocumentChangeResult::removed($component, $resourceType, $resourceId);
    }
}
