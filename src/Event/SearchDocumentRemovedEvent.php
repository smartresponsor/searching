<?php

declare(strict_types=1);

namespace App\Searching\Event;

/**
 * Defines the search document removed event responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchDocumentRemovedEvent
{
    public function __construct(
        public string $component,
        public string $resourceType,
        public string $resourceId,
        public string $changeReason = 'removed',
    ) {
    }
}
