<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Registry;

final readonly class SearchableResourceDefinition
{
    public function __construct(
        public string $component,
        public string $resourceType,
        public string $providerClass,
    ) {
    }

    public function key(): string
    {
        return $this->component.':'.$this->resourceType;
    }
}
