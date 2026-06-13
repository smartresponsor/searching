<?php

declare(strict_types=1);

namespace App\Searching\Event;

use App\Searching\Value\Document\SearchDocument;

final readonly class SearchDocumentChangedEvent
{
    public function __construct(
        public SearchDocument $document,
        public string $changeReason = 'changed',
    ) {
    }
}
