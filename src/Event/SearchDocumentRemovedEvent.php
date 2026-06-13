<?php

declare(strict_types=1);

namespace App\Searching\Event;

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
