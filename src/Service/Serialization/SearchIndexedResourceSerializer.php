<?php

declare(strict_types=1);

namespace App\Searching\Service\Serialization;

final readonly class SearchIndexedResourceSerializer
{
    /**
     * @return array<string, mixed>
     */
    public function serialize(SearchIndexedResourceEntity $resource): array
    {
        $indexedAt = $resource->getIndexedAt();
        $sourceUpdatedAt = $resource->getSourceUpdatedAt();

        return [
            'id' => $resource->getId(),
            'component' => $resource->getComponent(),
            'resourceType' => $resource->getResourceType(),
            'resourceId' => $resource->getResourceId(),
            'documentHash' => $resource->getDocumentHash(),
            'indexedAt' => $indexedAt?->format(DATE_ATOM),
            'sourceUpdatedAt' => $sourceUpdatedAt?->format(DATE_ATOM),
            'status' => $resource->getStatus(),
            'stale' => $this->isStale($resource),
            'errorMessage' => $resource->getErrorMessage(),
            'createdAt' => $resource->getCreatedAt()->format(DATE_ATOM),
            'updatedAt' => $resource->getUpdatedAt()->format(DATE_ATOM),
        ];
    }

    /**
     * @param iterable<SearchIndexedResourceEntity> $resources
     *
     * @return list<array<string, mixed>>
     */
    public function serializeList(iterable $resources): array
    {
        $payload = [];
        foreach ($resources as $resource) {
            $payload[] = $this->serialize($resource);
        }

        return $payload;
    }

    private function isStale(SearchIndexedResourceEntity $resource): bool
    {
        if ('removed' === $resource->getStatus()) {
            return false;
        }

        $indexedAt = $resource->getIndexedAt();
        $sourceUpdatedAt = $resource->getSourceUpdatedAt();

        if (null === $indexedAt) {
            return true;
        }

        return null !== $sourceUpdatedAt && $sourceUpdatedAt > $indexedAt;
    }
}
