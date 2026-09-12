<?php

declare(strict_types=1);

namespace App\Searching\Value\Indexing;

final readonly class SearchDocumentChangeResult
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public string $operation,
        public string $component,
        public string $resourceType,
        public string $resourceId,
        public string $status,
        public array $metadata = [],
    ) {
    }

    /** @param array<string, mixed> $metadata */
    public static function indexed(string $component, string $resourceType, string $resourceId, array $metadata = []): self
    {
        return new self('index', $component, $resourceType, $resourceId, 'indexed', $metadata);
    }

    /** @param array<string, mixed> $metadata */
    public static function removed(string $component, string $resourceType, string $resourceId, array $metadata = []): self
    {
        return new self('delete', $component, $resourceType, $resourceId, 'removed', $metadata);
    }
}
