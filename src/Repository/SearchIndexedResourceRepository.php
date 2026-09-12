<?php

declare(strict_types=1);

namespace App\Searching\Repository;

use App\Searching\Entity\SearchIndexedResourceEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<SearchIndexedResourceEntity> */
final class SearchIndexedResourceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SearchIndexedResourceEntity::class);
    }

    public function findOneByIdentity(string $component, string $resourceType, string $resourceId): ?SearchIndexedResourceEntity
    {
        return $this->findOneBy([
            'component' => $component,
            'resourceType' => $resourceType,
            'resourceId' => $resourceId,
        ]);
    }

    public function getOrCreate(string $component, string $resourceType, string $resourceId): SearchIndexedResourceEntity
    {
        return $this->findOneByIdentity($component, $resourceType, $resourceId)
            ?? new SearchIndexedResourceEntity($component, $resourceType, $resourceId);
    }

    /**
     * @return list<SearchIndexedResourceEntity>
     */
    public function findStaleSince(\DateTimeImmutable $threshold, int $limit = 100): array
    {
        /** @var list<SearchIndexedResourceEntity> $resources */
        $resources = $this->createQueryBuilder('resource')
            ->andWhere('resource.status = :status')
            ->andWhere('resource.sourceUpdatedAt < :threshold OR resource.indexedAt IS NULL')
            ->setParameter('status', 'indexed')
            ->setParameter('threshold', $threshold)
            ->orderBy('resource.sourceUpdatedAt', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $resources;
    }

    /**
     * @return list<SearchIndexedResourceEntity>
     */
    public function findByCriteria(\App\Searching\Value\Indexing\SearchIndexedResourceCriteria $criteria): array
    {
        $qb = $this->createQueryBuilder('resource');
        $this->applyCriteria($qb, $criteria);

        /** @var list<SearchIndexedResourceEntity> $resources */
        $resources = $qb
            ->orderBy('resource.updatedAt', 'DESC')
            ->addOrderBy('resource.id', 'DESC')
            ->setMaxResults($criteria->limit)
            ->setFirstResult($criteria->offset)
            ->getQuery()
            ->getResult();

        return $resources;
    }

    public function countByCriteria(\App\Searching\Value\Indexing\SearchIndexedResourceCriteria $criteria): int
    {
        $qb = $this->createQueryBuilder('resource')
            ->select('COUNT(resource.id)');
        $this->applyCriteria($qb, $criteria);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function applyCriteria(\Doctrine\ORM\QueryBuilder $qb, \App\Searching\Value\Indexing\SearchIndexedResourceCriteria $criteria): void
    {
        if (null !== $criteria->component) {
            $qb->andWhere('resource.component = :component')
                ->setParameter('component', $criteria->component);
        }

        if (null !== $criteria->resourceType) {
            $qb->andWhere('resource.resourceType = :resourceType')
                ->setParameter('resourceType', $criteria->resourceType);
        }

        if (null !== $criteria->resourceId) {
            $qb->andWhere('resource.resourceId = :resourceId')
                ->setParameter('resourceId', $criteria->resourceId);
        }

        if (null !== $criteria->status) {
            $qb->andWhere('resource.status = :status')
                ->setParameter('status', $criteria->status);
        }

        if (null !== $criteria->indexedFrom) {
            $qb->andWhere('resource.indexedAt >= :indexedFrom')
                ->setParameter('indexedFrom', $criteria->indexedFrom);
        }

        if (null !== $criteria->indexedTo) {
            $qb->andWhere('resource.indexedAt <= :indexedTo')
                ->setParameter('indexedTo', $criteria->indexedTo);
        }

        if (true === $criteria->stale) {
            $qb->andWhere('resource.status != :removedStatus')
                ->andWhere('resource.indexedAt IS NULL OR resource.sourceUpdatedAt > resource.indexedAt')
                ->setParameter('removedStatus', 'removed');
        } elseif (false === $criteria->stale) {
            $qb->andWhere('resource.indexedAt IS NOT NULL')
                ->andWhere('resource.sourceUpdatedAt IS NULL OR resource.sourceUpdatedAt <= resource.indexedAt');
        }
    }
}
