<?php

declare(strict_types=1);

namespace App\Searching\Service\Serialization;

use App\Searching\Entity\SearchIndexEntity;

/**
 * Defines the search index serializer responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchIndexSerializer
{
    /**
     * @return array<string, mixed>
     */
    public function serialize(SearchIndexEntity $index): array
    {
        $lastIndexedAt = $index->getLastIndexedAt();
        $lastLifecycleAt = $index->getLastLifecycleAt();

        return [
            'id' => $index->getId(),
            'nameEntity' => $index->getName(),
            'provider' => $index->getProvider(),
            'indexName' => $index->getIndexName(),
            'component' => $index->getComponent(),
            'resourceType' => $index->getResourceType(),
            'enabled' => $index->isEnabled(),
            'lifecycleStatus' => $index->getLifecycleStatus(),
            'lastLifecycleOperation' => $index->getLastLifecycleOperation(),
            'lastLifecycleAt' => $lastLifecycleAt?->format(DATE_ATOM),
            'lastLifecycleError' => $index->getLastLifecycleError(),
            'lastIndexedAt' => $lastIndexedAt?->format(DATE_ATOM),
            'createdAt' => $index->getCreatedAt()->format(DATE_ATOM),
            'updatedAt' => $index->getUpdatedAt()->format(DATE_ATOM),
        ];
    }

    /**
     * @param iterable<SearchIndexEntity> $indexes
     *
     * @return list<array<string, mixed>>
     */
    public function serializeList(iterable $indexes): array
    {
        $payload = [];
        foreach ($indexes as $index) {
            $payload[] = $this->serialize($index);
        }

        return $payload;
    }
}
