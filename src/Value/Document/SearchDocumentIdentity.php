<?php

declare(strict_types=1);

namespace App\Searching\Value\Document;

final readonly class SearchDocumentIdentity
{
    public function __construct(
        public string $component,
        public string $resourceType,
        public string $resourceId,
    ) {
    }

    public function toKey(): string
    {
        return $this->component.':'.$this->resourceType.':'.$this->resourceId;
    }
}
