<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Indexing;

/**
 * Defines the search reindex dispatch result responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchReindexDispatchResult
{
    /** @param array<string, mixed> $metadata */
    public function __construct(
        public string $jobId,
        public string $mode,
        public bool $queued,
        public ?SearchReindexResult $syncResult = null,
        public array $metadata = [],
    ) {
    }
}
