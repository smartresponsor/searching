<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Query;

use App\Searching\ValueObject\Observability\SearchExecutionContext;

final readonly class SearchQueryExecutionTrace
{
    /**
     * @param array<string, mixed> $providerMetadata
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public string $query,
        public ?string $userId,
        public ?string $vendorId,
        public string $providerName,
        public int $providerTotal,
        public int $returnedTotal,
        public int $deniedCount,
        public float $durationMs,
        public \DateTimeImmutable $executedAt,
        public bool $successful = true,
        public ?string $errorClass = null,
        public ?string $errorMessage = null,
        public array $providerMetadata = [],
        public array $metadata = [],
        public ?SearchExecutionContext $executionContext = null,
    ) {
    }
}
