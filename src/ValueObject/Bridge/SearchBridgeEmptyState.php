<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Bridge;

final readonly class SearchBridgeEmptyState
{
    /**
     * @param array<string, mixed> $actionRouteParameters
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public string $title,
        public string $message,
        public ?string $actionLabel = null,
        public ?string $actionRouteName = null,
        public array $actionRouteParameters = [],
        public array $metadata = [],
    ) {
    }
}
