<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Provider;

/**
 * Defines the search index mapping responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchIndexMapping
{
    /**
     * @param array<string, mixed> $settings
     * @param array<string, mixed> $properties
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public string $indexName,
        public array $settings,
        public array $properties,
        public array $metadata = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        return [
            'settings' => $this->settings,
            'mappings' => [
                '_meta' => $this->metadata,
                'properties' => $this->properties,
            ],
        ];
    }
}
