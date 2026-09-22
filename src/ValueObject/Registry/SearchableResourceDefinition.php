<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Registry;

/**
 * Defines the searchable resource definition responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchableResourceDefinition
{
    public function __construct(
        public string $component,
        public string $resourceType,
        public string $providerClass,
    ) {
    }

    /**
     * Executes the key responsibility defined by the Searching component contract.
     */
    public function key(): string
    {
        return $this->component.':'.$this->resourceType;
    }
}
