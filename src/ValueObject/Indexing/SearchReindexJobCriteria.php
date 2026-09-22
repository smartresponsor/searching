<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Indexing;

final readonly class SearchReindexJobCriteria
{
    public function __construct(
        public ?string $jobKey = null,
        public ?string $component = null,
        public ?string $resourceType = null,
        public ?string $status = null,
        public ?string $requestedBy = null,
        public ?\DateTimeImmutable $createdFrom = null,
        public ?\DateTimeImmutable $createdTo = null,
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
            jobKey: self::nullableString($input['jobKey'] ?? $input['job_key'] ?? $input['jobId'] ?? $input['job_id'] ?? null),
            component: self::nullableString($input['component'] ?? null),
            resourceType: self::nullableString($input['resourceType'] ?? $input['resource_type'] ?? $input['resource'] ?? null),
            status: self::nullableString($input['status'] ?? null),
            requestedBy: self::nullableString($input['requestedBy'] ?? $input['requested_by'] ?? null),
            createdFrom: self::nullableDate($input['createdFrom'] ?? $input['created_from'] ?? $input['from'] ?? null),
            createdTo: self::nullableDate($input['createdTo'] ?? $input['created_to'] ?? $input['to'] ?? null),
            limit: min($limit, 500),
            offset: $offset,
        );
    }

    private static function nullableString(mixed $value): ?string
    {
        if (null === $value || !is_scalar($value)) {
            return null;
        }

        $string = trim((string) $value);

        return '' === $string ? null : $string;
    }

    private static function nullableDate(mixed $value): ?\DateTimeImmutable
    {
        $string = self::nullableString($value);
        if (null === $string) {
            return null;
        }

        try {
            return new \DateTimeImmutable($string);
        } catch (\Throwable) {
            return null;
        }
    }

    private static function toPositiveInt(mixed $value, int $default): int
    {
        if (null === $value || '' === $value) {
            return $default;
        }

        if (!is_int($value) && !is_string($value) && !is_float($value)) {
            return $default;
        }

        $int = filter_var($value, FILTER_VALIDATE_INT);
        if (false === $int) {
            return $default;
        }

        return $int > 0 ? $int : $default;
    }
}
