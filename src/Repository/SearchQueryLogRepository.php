<?php

declare(strict_types=1);

namespace App\Searching\Repository;

use App\Searching\Entity\SearchQueryLogEntity;
use App\Searching\Value\Query\SearchQueryLogCriteria;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

final class SearchQueryLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SearchQueryLogEntity::class);
    }

    /**
     * @return list<SearchQueryLogEntity>
     */
    public function findByCriteria(SearchQueryLogCriteria $criteria): array
    {
        $queryBuilder = $this->createCriteriaQueryBuilder($criteria);
        $queryBuilder
            ->orderBy('queryLog.createdAt', 'DESC')
            ->setMaxResults($criteria->limit)
            ->setFirstResult($criteria->offset);

        /** @var list<SearchQueryLogEntity> $result */
        $result = $queryBuilder->getQuery()->getResult();

        return $result;
    }

    public function countByCriteria(SearchQueryLogCriteria $criteria): int
    {
        $queryBuilder = $this->createCriteriaQueryBuilder($criteria);
        $queryBuilder->select('COUNT(queryLog.id)');

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    private function createCriteriaQueryBuilder(SearchQueryLogCriteria $criteria): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('queryLog');

        if (null !== $criteria->query) {
            $queryBuilder
                ->andWhere('queryLog.queryText LIKE :queryText')
                ->setParameter('queryText', '%'.$criteria->query.'%');
        }

        if (null !== $criteria->userId) {
            $queryBuilder
                ->andWhere('queryLog.userId = :userId')
                ->setParameter('userId', $criteria->userId);
        }

        if (null !== $criteria->tenantId) {
            $queryBuilder
                ->andWhere('queryLog.tenantId = :tenantId')
                ->setParameter('tenantId', $criteria->tenantId);
        }

        if (null !== $criteria->providerName) {
            $queryBuilder
                ->andWhere('queryLog.providerName = :providerName')
                ->setParameter('providerName', $criteria->providerName);
        }

        if (null !== $criteria->correlationId) {
            $queryBuilder
                ->andWhere('queryLog.correlationId = :correlationId')
                ->setParameter('correlationId', $criteria->correlationId);
        }

        if (null !== $criteria->requestId) {
            $queryBuilder
                ->andWhere('queryLog.requestId = :requestId')
                ->setParameter('requestId', $criteria->requestId);
        }

        if (null !== $criteria->sourceComponent) {
            $queryBuilder
                ->andWhere('queryLog.sourceComponent = :sourceComponent')
                ->setParameter('sourceComponent', $criteria->sourceComponent);
        }

        if (null !== $criteria->sourceOperation) {
            $queryBuilder
                ->andWhere('queryLog.sourceOperation = :sourceOperation')
                ->setParameter('sourceOperation', $criteria->sourceOperation);
        }

        if (null !== $criteria->successful) {
            $queryBuilder
                ->andWhere('queryLog.successful = :successful')
                ->setParameter('successful', $criteria->successful);
        }

        if (null !== $criteria->from) {
            $queryBuilder
                ->andWhere('queryLog.createdAt >= :fromDate')
                ->setParameter('fromDate', $criteria->from);
        }

        if (null !== $criteria->to) {
            $queryBuilder
                ->andWhere('queryLog.createdAt <= :toDate')
                ->setParameter('toDate', $criteria->to);
        }

        return $queryBuilder;
    }
}
