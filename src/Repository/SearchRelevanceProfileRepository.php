<?php

declare(strict_types=1);

namespace App\Searching\Repository;

use App\Searching\Entity\SearchRelevanceProfileEntity;
use App\Searching\Value\Tuning\SearchRelevanceProfileCriteria;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<SearchRelevanceProfileEntity> */
final class SearchRelevanceProfileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SearchRelevanceProfileEntity::class);
    }

    /**
     * @return list<SearchRelevanceProfileEntity>
     */
    public function findByCriteria(SearchRelevanceProfileCriteria $criteria): array
    {
        $queryBuilder = $this->createCriteriaQueryBuilder($criteria);
        $queryBuilder
            ->orderBy('profile.nameEntity', 'ASC')
            ->setMaxResults($criteria->limit)
            ->setFirstResult($criteria->offset);

        /** @var list<SearchRelevanceProfileEntity> $result */
        $result = $queryBuilder->getQuery()->getResult();

        return $result;
    }

    public function countByCriteria(SearchRelevanceProfileCriteria $criteria): int
    {
        $queryBuilder = $this->createCriteriaQueryBuilder($criteria);
        $queryBuilder->select('COUNT(profile.id)');

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    private function createCriteriaQueryBuilder(SearchRelevanceProfileCriteria $criteria): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('profile');

        if (null !== $criteria->nameEntity) {
            $queryBuilder
                ->andWhere('profile.nameEntity LIKE :nameEntity')
                ->setParameter('nameEntity', '%'.$criteria->nameEntity.'%');
        }

        if (null !== $criteria->component) {
            $queryBuilder
                ->andWhere('profile.component = :component')
                ->setParameter('component', $criteria->component);
        }

        if (null !== $criteria->resourceType) {
            $queryBuilder
                ->andWhere('profile.resourceType = :resourceType')
                ->setParameter('resourceType', $criteria->resourceType);
        }

        if (null !== $criteria->enabled) {
            $queryBuilder
                ->andWhere('profile.enabled = :enabled')
                ->setParameter('enabled', $criteria->enabled);
        }

        return $queryBuilder;
    }
}
