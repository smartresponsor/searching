<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Document;

final readonly class SearchDocument
{
    /**
     * @param list<string>         $keywords
     * @param array<string, mixed> $facets
     * @param list<string>         $permissions
     * @param array<string, mixed> $routeParameters
     */
    public function __construct(
        public string $component,
        public string $resourceType,
        public string $resourceId,
        public string $title,
        public ?string $summary,
        public ?string $body,
        public array $keywords,
        public array $facets,
        public array $permissions,
        public ?string $locale,
        public ?string $vendorId,
        public ?string $ownerId,
        public string $routeName,
        public array $routeParameters,
        public \DateTimeImmutable $updatedAt,
    ) {
    }
}
