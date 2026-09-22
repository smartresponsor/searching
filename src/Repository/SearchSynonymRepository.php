<?php

declare(strict_types=1);

namespace App\Searching\Repository;

use App\Searching\Entity\SearchSynonymEntity;
use App\Searching\ValueObject\Tuning\SearchSynonymCriteria;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<SearchSynonymEntity> */
final class SearchSynonymRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SearchSynonymEntity::class);
    }

    /**
     * @return list<SearchSynonymEntity>
     */
    public function findByCriteria(SearchSynonymCriteria $criteria): array
    {
        $queryBuilder = $this->createCriteriaQueryBuilder($criteria);
        $queryBuilder
            ->orderBy('synonym.sourceTerm', 'ASC')
            ->setMaxResults($criteria->limit)
            ->setFirstResult($criteria->offset);

        /** @var list<SearchSynonymEntity> $result */
        $result = $queryBuilder->getQuery()->getResult();

        return $result;
    }

    public function countByCriteria(SearchSynonymCriteria $criteria): int
    {
        $queryBuilder = $this->createCriteriaQueryBuilder($criteria);
        $queryBuilder->select('COUNT(synonym.id)');

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    private function createCriteriaQueryBuilder(SearchSynonymCriteria $criteria): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('synonym');

        if (null !== $criteria->locale) {
            $queryBuilder
                ->andWhere('synonym.locale = :locale')
                ->setParameter('locale', $criteria->locale);
        }

        if (null !== $criteria->sourceTerm) {
            $queryBuilder
                ->andWhere('synonym.sourceTerm LIKE :sourceTerm')
                ->setParameter('sourceTerm', '%'.$criteria->sourceTerm.'%');
        }

        if (null !== $criteria->enabled) {
            $queryBuilder
                ->andWhere('synonym.enabled = :enabled')
                ->setParameter('enabled', $criteria->enabled);
        }

        return $queryBuilder;
    }
}
