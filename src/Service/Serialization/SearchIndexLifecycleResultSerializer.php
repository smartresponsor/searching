<?php

declare(strict_types=1);

namespace App\Searching\Service\Serialization;

use App\Searching\ValueObject\Provider\SearchIndexLifecycleResult;

/**
 * Defines the search index lifecycle result serializer responsibility within the Searching component runtime and its typed boundaries.
 */
final class SearchIndexLifecycleResultSerializer
{
    /**
     * @return array<string, mixed>
     */
    public function serialize(SearchIndexLifecycleResult $result): array
    {
        return $result->toArray();
    }

    /**
     * @param array<string, SearchIndexLifecycleResult> $results
     *
     * @return array<string, array<string, mixed>>
     */
    public function serializeMany(array $results): array
    {
        $serialized = [];
        foreach ($results as $providerName => $result) {
            $serialized[$providerName] = $this->serialize($result);
        }

        return $serialized;
    }
}
