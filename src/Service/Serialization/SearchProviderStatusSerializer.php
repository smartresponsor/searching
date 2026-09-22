<?php

declare(strict_types=1);

namespace App\Searching\Service\Serialization;

use App\Searching\ValueObject\Provider\SearchProviderStatus;

/**
 * Defines the search provider status serializer responsibility within the Searching component runtime and its typed boundaries.
 */
final class SearchProviderStatusSerializer
{
    /**
     * @param array<string, SearchProviderStatus> $statuses
     *
     * @return array<string, mixed>
     */
    public function serializeStatuses(array $statuses): array
    {
        $serialized = [];

        foreach ($statuses as $nameEntity => $status) {
            $serialized[$nameEntity] = $this->serializeStatus($status);
        }

        return [
            'total' => count($serialized),
            'providers' => $serialized,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function serializeStatus(SearchProviderStatus $status): array
    {
        return [
            'nameEntity' => $status->nameEntity,
            'available' => $status->available,
            'status' => $status->status,
            'metadata' => $status->metadata,
        ];
    }
}
