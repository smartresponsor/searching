<?php

declare(strict_types=1);

namespace App\Searching\Value\Indexing;

final readonly class SearchReindexDispatchResult
{
    public function __construct(
        public string $jobId,
        public string $mode,
        public bool $queued,
        public ?SearchReindexResult $syncResult = null,
        public array $metadata = [],
    ) {
    }
}
