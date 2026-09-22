<?php

declare(strict_types=1);

namespace App\Searching\Service\Serialization;

use App\Searching\ValueObject\Indexing\SearchReindexDispatchResult;

/**
 * Defines the search reindex dispatch result serializer responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchReindexDispatchResultSerializer
{
    public function __construct(private SearchReindexResultSerializer $syncResultSerializer = new SearchReindexResultSerializer())
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function serialize(SearchReindexDispatchResult $result): array
    {
        return [
            'jobId' => $result->jobId,
            'mode' => $result->mode,
            'queued' => $result->queued,
            'syncResult' => null !== $result->syncResult ? $this->syncResultSerializer->serialize($result->syncResult) : null,
            'metadata' => $result->metadata,
        ];
    }
}
