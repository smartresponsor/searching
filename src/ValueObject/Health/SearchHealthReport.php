<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Health;

final readonly class SearchHealthReport
{
    /**
     * @param list<SearchHealthIndicator> $indicators
     * @param array<string, mixed>        $metadata
     */
    public function __construct(
        public string $status,
        public array $indicators,
        public \DateTimeImmutable $checkedAt,
        public array $metadata = [],
    ) {
    }

    public function isReady(): bool
    {
        return 'healthy' === $this->status || 'degraded' === $this->status;
    }
}
