<?php

declare(strict_types=1);

namespace App\Searching\Value\Tuning;

final readonly class SearchRelevanceProfileCriteria
{
    public function __construct(
        public ?string $nameEntity = null,
        public ?string $component = null,
        public ?string $resourceType = null,
        public ?bool $enabled = null,
        public int $limit = 50,
        public int $offset = 0,
    ) {
    }

    /**
     * @param array<string, mixed> $input
     */
    public static function fromArray(array $input, int $defaultLimit = 50): self
    {
        return new self(
            name: self::stringOrNull($input['nameEntity'] ?? $input['query'] ?? null),
            component: self::stringOrNull($input['component'] ?? null),
            resourceType: self::stringOrNull($input['resourceType'] ?? $input['resource_type'] ?? null),
            enabled: self::boolOrNull($input['enabled'] ?? null),
            limit: max(1, min(200, (int) ($input['limit'] ?? $defaultLimit))),
            offset: max(0, (int) ($input['offset'] ?? 0)),
        );
    }

    private static function stringOrNull(mixed $value): ?string
    {
        if (null === $value) {
            return null;
        }

        $value = trim((string) $value);

        return '' === $value ? null : $value;
    }

    private static function boolOrNull(mixed $value): ?bool
    {
        if (null === $value || '' === $value) {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        return filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
    }
}
