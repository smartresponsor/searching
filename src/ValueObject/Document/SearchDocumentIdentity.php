<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Document;

/**
 * Defines the search document identity responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchDocumentIdentity
{
    public function __construct(
        public string $component,
        public string $resourceType,
        public string $resourceId,
    ) {
    }

    /**
     * Executes the to key responsibility defined by the Searching component contract.
     */
    public function toKey(): string
    {
        return $this->component.':'.$this->resourceType.':'.$this->resourceId;
    }
}
