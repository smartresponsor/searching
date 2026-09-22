<?php

declare(strict_types=1);

namespace App\Searching\EventSubscriber;

use App\Searching\Contract\Indexing\SearchIncrementalIndexerInterface;
use App\Searching\Event\SearchDocumentChangedEvent;
use App\Searching\Event\SearchDocumentRemovedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class SearchDocumentChangeSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private SearchIncrementalIndexerInterface $incrementalIndexer,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SearchDocumentChangedEvent::class => 'onDocumentChanged',
            SearchDocumentRemovedEvent::class => 'onDocumentRemoved',
        ];
    }

    public function onDocumentChanged(SearchDocumentChangedEvent $event): void
    {
        $this->incrementalIndexer->indexChangedDocument($event->document, $event->changeReason);
    }

    public function onDocumentRemoved(SearchDocumentRemovedEvent $event): void
    {
        $this->incrementalIndexer->removeResource(
            component: $event->component,
            resourceType: $event->resourceType,
            resourceId: $event->resourceId,
            changeReason: $event->changeReason,
        );
    }
}
