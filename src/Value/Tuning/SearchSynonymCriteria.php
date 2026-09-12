<?php

declare(strict_types=1);

namespace App\Searching\Value\Tuning;

final readonly class SearchSynonymCriteria
{
    public function __construct(
        public ?string $locale = null,
        public ?string $sourceTerm = null,
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
            locale: self::stringOrNull($input['locale'] ?? null),
            sourceTerm: self::stringOrNull($input['sourceTerm'] ?? $input['source_term'] ?? $input['query'] ?? null),
            enabled: self::boolOrNull($input['enabled'] ?? null),
            limit: min(200, self::toInt($input['limit'] ?? null, $defaultLimit, 1)),
            offset: self::toInt($input['offset'] ?? null, 0, 0),
        );
    }

    private static function stringOrNull(mixed $value): ?string
    {
        if (null === $value || !is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return '' === $value ? null : $value;
    }

    private static function toInt(mixed $value, int $default, int $minimum): int
    {
        if (!is_int($value) && !is_string($value) && !is_float($value)) {
            return $default;
        }

        $integer = filter_var($value, FILTER_VALIDATE_INT);

        return false === $integer ? $default : max($minimum, $integer);
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
