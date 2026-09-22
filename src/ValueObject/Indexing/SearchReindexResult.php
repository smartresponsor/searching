<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Indexing;

use App\Searching\ValueObject\Observability\SearchExecutionContext;

/**
 * Defines the search reindex result responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchReindexResult
{
    /**
     * @param list<string> $errors
     */
    public function __construct(
        public string $jobId,
        public int $providerCount,
        public int $documentCount,
        public int $failedCount = 0,
        public array $errors = [],
        public ?SearchExecutionContext $executionContext = null,
    ) {
    }

    public function isSuccessful(): bool
    {
        return 0 === $this->failedCount;
    }
}
