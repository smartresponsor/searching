<?php

declare(strict_types=1);

namespace App\Searching\Service\Serialization;

use App\Searching\ValueObject\Indexing\SearchReindexResult;

/**
 * Defines the search reindex result serializer responsibility within the Searching component runtime and its typed boundaries.
 */
final class SearchReindexResultSerializer
{
    /**
     * @return array<string, mixed>
     */
    public function serialize(SearchReindexResult $result): array
    {
        return [
            'jobId' => $result->jobId,
            'successful' => $result->isSuccessful(),
            'providerCount' => $result->providerCount,
            'documentCount' => $result->documentCount,
            'failedCount' => $result->failedCount,
            'errors' => $result->errors,
            'metadata' => [
                'execution_context' => $result->executionContext?->toMetadata() ?? [],
            ],
        ];
    }
}
