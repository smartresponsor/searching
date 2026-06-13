<?php

declare(strict_types=1);

namespace App\Searching\Value\Indexing;

final readonly class SearchIndexCriteria
{
    public function __construct(
        public ?string $provider = null,
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
        $limit = self::toPositiveInt($input['limit'] ?? null, $defaultLimit);
        $offset = max(0, self::toPositiveInt($input['offset'] ?? null, 0));

        return new self(
            provider: self::nullableString($input['provider'] ?? null),
            component: self::nullableString($input['component'] ?? null),
            resourceType: self::nullableString($input['resourceType'] ?? $input['resource_type'] ?? $input['resource'] ?? null),
            enabled: self::nullableBool($input['enabled'] ?? null),
            limit: min($limit, 500),
            offset: $offset,
        );
    }

    private static function nullableString(mixed $value): ?string
    {
        if (null === $value) {
            return null;
        }

        $string = trim((string) $value);

        return '' === $string ? null : $string;
    }

    private static function nullableBool(mixed $value): ?bool
    {
        if (null === $value || '' === $value) {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }

    private static function toPositiveInt(mixed $value, int $default): int
    {
        if (null === $value || '' === $value) {
            return $default;
        }

        $int = (int) $value;

        return $int > 0 ? $int : $default;
    }
}
