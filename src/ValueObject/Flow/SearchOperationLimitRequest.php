<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Flow;

use App\Searching\ValueObject\Query\SearchQuery;

final readonly class SearchOperationLimitRequest
{
    /** @param array<string, mixed> $metadata */
    public function __construct(
        public string $operation,
        public ?string $identity = null,
        public int $cost = 1,
        public array $metadata = [],
    ) {
        if ($cost < 1) {
            throw new \InvalidArgumentException('Search operation limit cost must be greater than zero.');
        }
    }

    public static function forSearchQuery(SearchQuery $query): self
    {
        return new self(
            operation: 'search.query',
            identity: self::identity($query->userId, $query->vendorId, $query->query),
            cost: max(1, min(10, $query->limit)),
            metadata: [
                'query' => $query->query,
                'user_id' => $query->userId,
                'vendor_id' => $query->vendorId,
                'components' => $query->components,
                'resource_types' => $query->resourceTypes,
                'limit' => $query->limit,
            ],
        );
    }

    public static function forReindexDispatch(
        ?string $component = null,
        ?string $resourceType = null,
        ?\DateTimeImmutable $changedSince = null,
        ?string $requestedBy = null,
    ): self {
        return new self(
            operation: 'search.reindex.dispatch',
            identity: self::identity($requestedBy, $component, $resourceType),
            cost: null !== $component && null !== $resourceType ? 20 : 100,
            metadata: [
                'component' => $component,
                'resource_type' => $resourceType,
                'changed_since' => $changedSince?->format(\DateTimeInterface::ATOM),
                'requested_by' => $requestedBy,
            ],
        );
    }

    private static function identity(?string ...$parts): string
    {
        $normalized = array_values(array_filter(array_map(static fn (?string $part): string => trim((string) $part), $parts), static fn (string $part): bool => '' !== $part));

        return [] === $normalized ? 'anonymous' : implode(':', $normalized);
    }
}
