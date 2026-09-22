<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Observability;

final readonly class SearchExecutionContext
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public string $correlationId,
        public ?string $requestId = null,
        public ?string $sourceComponent = null,
        public ?string $sourceOperation = null,
        public ?string $actorId = null,
        public array $metadata = [],
    ) {
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public static function create(?string $correlationId = null, ?string $requestId = null, ?string $sourceComponent = null, ?string $sourceOperation = null, ?string $actorId = null, array $metadata = []): self
    {
        return new self(
            correlationId: self::nonEmpty($correlationId) ?? self::generateId('srch'),
            requestId: self::nonEmpty($requestId),
            sourceComponent: self::nonEmpty($sourceComponent),
            sourceOperation: self::nonEmpty($sourceOperation),
            actorId: self::nonEmpty($actorId),
            metadata: $metadata,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toMetadata(): array
    {
        return array_filter([
            'correlation_id' => $this->correlationId,
            'request_id' => $this->requestId,
            'source_component' => $this->sourceComponent,
            'source_operation' => $this->sourceOperation,
            'actor_id' => $this->actorId,
            'metadata' => $this->metadata,
        ], static fn (mixed $value): bool => null !== $value && [] !== $value);
    }

    public function withSourceOperation(string $sourceOperation): self
    {
        return new self(
            correlationId: $this->correlationId,
            requestId: $this->requestId,
            sourceComponent: $this->sourceComponent,
            sourceOperation: $sourceOperation,
            actorId: $this->actorId,
            metadata: $this->metadata,
        );
    }

    private static function nonEmpty(?string $value): ?string
    {
        if (null === $value) {
            return null;
        }

        $value = trim($value);

        return '' === $value ? null : $value;
    }

    private static function generateId(string $prefix): string
    {
        try {
            return sprintf('%s_%s', $prefix, bin2hex(random_bytes(12)));
        } catch (\Throwable) {
            return sprintf('%s_%s_%s', $prefix, date('YmdHis'), str_replace('.', '', uniqid('', true)));
        }
    }
}
