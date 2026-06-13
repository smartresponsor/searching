<?php

declare(strict_types=1);

namespace App\Searching\Value\Surface;

final readonly class SearchSurfaceCapability
{
    /**
     * @param list<string>         $supportedFeatures
     * @param list<string>         $supportedComponents
     * @param list<string>         $supportedResourceTypes
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public bool $enabled,
        public string $providerName,
        public array $supportedFeatures = [],
        public array $supportedComponents = [],
        public array $supportedResourceTypes = [],
        public array $metadata = [],
    ) {
    }
}
