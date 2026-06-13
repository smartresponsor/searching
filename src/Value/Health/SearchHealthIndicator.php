<?php

declare(strict_types=1);

namespace App\Searching\Value\Health;

final readonly class SearchHealthIndicator
{
    /**
     * @param array<string, int|float|string|bool|null> $metrics
     * @param array<string, mixed>                      $metadata
     */
    public function __construct(
        public string $nameEntity,
        public string $status,
        public string $summary,
        public array $metrics = [],
        public array $metadata = [],
    ) {
    }

    public function isHealthy(): bool
    {
        return 'healthy' === $this->status;
    }

    public function isUnhealthy(): bool
    {
        return 'unhealthy' === $this->status;
    }
}
