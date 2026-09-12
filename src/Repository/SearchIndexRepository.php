<?php

declare(strict_types=1);

namespace App\Searching\Repository;

use App\Searching\Entity\SearchIndexEntity;
use App\Searching\Value\Indexing\SearchIndexCriteria;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<SearchIndexEntity> */
final class SearchIndexRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SearchIndexEntity::class);
    }

    public function findOneByIdentity(string $provider, string $component, string $resourceType): ?SearchIndexEntity
    {
        return $this->findOneBy([
            'provider' => $provider,
            'component' => $component,
            'resourceType' => $resourceType,
        ]);
    }

    public function getOrCreate(string $provider, string $component, string $resourceType, string $nameEntity, string $indexName): SearchIndexEntity
    {
        return $this->findOneByIdentity($provider, $component, $resourceType)
            ?? new SearchIndexEntity($nameEntity, $provider, $indexName, $component, $resourceType);
    }

    /**
     * @return list<SearchIndexEntity>
     */
    public function findByCriteria(SearchIndexCriteria $criteria): array
    {
        $qb = $this->createQueryBuilder('searchIndex');
        $this->applyCriteria($qb, $criteria);

        /** @var list<SearchIndexEntity> $result */
        $result = $qb
            ->orderBy('searchIndex.provider', 'ASC')
            ->addOrderBy('searchIndex.component', 'ASC')
            ->addOrderBy('searchIndex.resourceType', 'ASC')
            ->setMaxResults($criteria->limit)
            ->setFirstResult($criteria->offset)
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function countByCriteria(SearchIndexCriteria $criteria): int
    {
        $qb = $this->createQueryBuilder('searchIndex')
            ->select('COUNT(searchIndex.id)');
        $this->applyCriteria($qb, $criteria);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function applyCriteria(QueryBuilder $qb, SearchIndexCriteria $criteria): void
    {
        if (null !== $criteria->provider) {
            $qb->andWhere('searchIndex.provider = :provider')
                ->setParameter('provider', $criteria->provider);
        }

        if (null !== $criteria->component) {
            $qb->andWhere('searchIndex.component = :component')
                ->setParameter('component', $criteria->component);
        }

        if (null !== $criteria->resourceType) {
            $qb->andWhere('searchIndex.resourceType = :resourceType')
                ->setParameter('resourceType', $criteria->resourceType);
        }

        if (null !== $criteria->enabled) {
            $qb->andWhere('searchIndex.enabled = :enabled')
                ->setParameter('enabled', $criteria->enabled);
        }
    }
}
