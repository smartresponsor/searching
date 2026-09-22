<?php

declare(strict_types=1);

namespace App\Searching\Repository;

use App\Searching\Contract\Indexing\SearchIndexedResourceTrackerInterface;
use App\Searching\Entity\SearchIndexedResourceEntity;
use App\Searching\ValueObject\Indexing\SearchDocumentFingerprint;
use App\Searching\ValueObject\Indexing\SearchIndexedResourceState;
use Doctrine\ORM\EntityManagerInterface;

final readonly class SearchDoctrineIndexedResourceTracker implements SearchIndexedResourceTrackerInterface
{
    public function __construct(
        private SearchIndexedResourceRepository $repository,
        private EntityManagerInterface $entityManager,
        private bool $flushImmediately = true,
    ) {
    }

    public function find(string $component, string $resourceType, string $resourceId): ?SearchIndexedResourceState
    {
        $resource = $this->repository->findOneByIdentity($component, $resourceType, $resourceId);

        return $resource instanceof SearchIndexedResourceEntity ? $this->stateFromEntity($resource) : null;
    }

    public function isCurrent(SearchDocumentFingerprint $fingerprint): bool
    {
        return $this->find($fingerprint->component, $fingerprint->resourceType, $fingerprint->resourceId)?->isCurrent($fingerprint) ?? false;
    }

    public function markIndexed(SearchDocumentFingerprint $fingerprint): SearchIndexedResourceState
    {
        $resource = $this->getResource($fingerprint);
        $resource->markIndexed($fingerprint->documentHash, $fingerprint->sourceUpdatedAt);
        $this->save($resource);

        return $this->stateFromEntity($resource);
    }

    public function markUnchanged(SearchDocumentFingerprint $fingerprint): SearchIndexedResourceState
    {
        $resource = $this->getResource($fingerprint);
        $resource->markUnchanged($fingerprint->documentHash, $fingerprint->sourceUpdatedAt);
        $this->save($resource);

        return $this->stateFromEntity($resource);
    }

    public function markFailed(SearchDocumentFingerprint $fingerprint, string $errorMessage): SearchIndexedResourceState
    {
        $resource = $this->getResource($fingerprint);
        $resource->markFailed($fingerprint->documentHash, $fingerprint->sourceUpdatedAt, $errorMessage);
        $this->save($resource);

        return $this->stateFromEntity($resource);
    }

    public function markRemoved(string $component, string $resourceType, string $resourceId): SearchIndexedResourceState
    {
        $resource = $this->repository->getOrCreate($component, $resourceType, $resourceId);
        $resource->markRemoved();
        $this->save($resource);

        return $this->stateFromEntity($resource);
    }

    private function getResource(SearchDocumentFingerprint $fingerprint): SearchIndexedResourceEntity
    {
        return $this->repository->getOrCreate($fingerprint->component, $fingerprint->resourceType, $fingerprint->resourceId);
    }

    private function save(SearchIndexedResourceEntity $resource): void
    {
        $this->entityManager->persist($resource);

        if ($this->flushImmediately) {
            $this->entityManager->flush();
        }
    }

    private function stateFromEntity(SearchIndexedResourceEntity $resource): SearchIndexedResourceState
    {
        return new SearchIndexedResourceState(
            component: $resource->getComponent(),
            resourceType: $resource->getResourceType(),
            resourceId: $resource->getResourceId(),
            documentHash: $resource->getDocumentHash(),
            indexedAt: $resource->getIndexedAt(),
            sourceUpdatedAt: $resource->getSourceUpdatedAt(),
            status: $resource->getStatus(),
            errorMessage: $resource->getErrorMessage(),
            metadata: [
                'id' => $resource->getId(),
                'created_at' => $resource->getCreatedAt()->format(\DateTimeInterface::ATOM),
                'updated_at' => $resource->getUpdatedAt()->format(\DateTimeInterface::ATOM),
            ],
        );
    }
}
