<?php

declare(strict_types=1);

namespace App\Searching\Event;

use App\Searching\ValueObject\Document\SearchDocument;

/**
 * Defines the search document changed event responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchDocumentChangedEvent
{
    public function __construct(
        public SearchDocument $document,
        public string $changeReason = 'changed',
    ) {
    }
}
