<?php

declare(strict_types=1);

namespace App\Searching\Value\Query;

final readonly class SearchQueryLogCriteria
{
    public function __construct(
        public int $limit = 50,
        public int $offset = 0,
        public ?string $query = null,
        public ?string $userId = null,
        public ?string $tenantId = null,
        public ?string $providerName = null,
        public ?string $correlationId = null,
        public ?string $requestId = null,
        public ?string $sourceComponent = null,
        public ?string $sourceOperation = null,
        public ?bool $successful = null,
        public ?\DateTimeImmutable $from = null,
        public ?\DateTimeImmutable $to = null,
    ) {
        if ($this->limit < 1) {
            throw new \InvalidArgumentException('Search query log limit must be greater than zero.');
        }

        if ($this->offset < 0) {
            throw new \InvalidArgumentException('Search query log offset must not be negative.');
        }
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public static function fromArray(array $parameters, int $defaultLimit = 50): self
    {
        return new self(
            limit: self::positiveInt($parameters['limit'] ?? $defaultLimit, $defaultLimit),
            offset: self::nonNegativeInt($parameters['offset'] ?? 0),
            query: self::nullableString($parameters['query'] ?? null),
            userId: self::nullableString($parameters['userId'] ?? $parameters['user_id'] ?? null),
            tenantId: self::nullableString($parameters['tenantId'] ?? $parameters['tenant_id'] ?? null),
            providerName: self::nullableString($parameters['providerName'] ?? $parameters['provider'] ?? null),
            correlationId: self::nullableString($parameters['correlationId'] ?? $parameters['correlation_id'] ?? null),
            requestId: self::nullableString($parameters['requestId'] ?? $parameters['request_id'] ?? null),
            sourceComponent: self::nullableString($parameters['sourceComponent'] ?? $parameters['source_component'] ?? null),
            sourceOperation: self::nullableString($parameters['sourceOperation'] ?? $parameters['source_operation'] ?? null),
            successful: self::nullableBool($parameters['successful'] ?? null),
            from: self::nullableDate($parameters['from'] ?? null),
            to: self::nullableDate($parameters['to'] ?? null),
        );
    }

    private static function positiveInt(mixed $value, int $fallback): int
    {
        if (null === $value || '' === $value) {
            return $fallback;
        }

        $integer = (int) $value;

        return max(1, $integer);
    }

    private static function nonNegativeInt(mixed $value): int
    {
        if (null === $value || '' === $value) {
            return 0;
        }

        return max(0, (int) $value);
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

        return filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
    }

    private static function nullableDate(mixed $value): ?\DateTimeImmutable
    {
        if (null === $value || '' === $value) {
            return null;
        }

        try {
            return new \DateTimeImmutable((string) $value);
        } catch (\Throwable) {
            return null;
        }
    }
}
