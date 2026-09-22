<?php

declare(strict_types=1);

namespace App\Searching\Builder\Indexing;

/**
 * Builds stable keys for semantically identical reindex dispatch requests.
 */
final readonly class SearchReindexIdempotencyKeyBuilder
{
    /**
     * Builds the build used by the Searching component execution and integration boundaries.
     */
    public function build(?string $component = null, ?string $resourceType = null, ?\DateTimeImmutable $changedSince = null): string
    {
        $payload = [
            'component' => $this->normalizeNullable($component),
            'resource_type' => $this->normalizeNullable($resourceType),
            'changed_since' => $changedSince?->format(\DateTimeInterface::ATOM),
        ];

        return hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR));
    }

    private function normalizeNullable(?string $value): ?string
    {
        if (null === $value) {
            return null;
        }

        $value = trim($value);

        return '' === $value ? null : strtolower($value);
    }
}
