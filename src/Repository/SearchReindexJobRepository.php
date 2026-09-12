<?php

declare(strict_types=1);

namespace App\Searching\Repository;

use App\Searching\Entity\SearchReindexJobEntity;
use App\Searching\Value\Indexing\SearchReindexJobCriteria;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<SearchReindexJobEntity> */
final class SearchReindexJobRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SearchReindexJobEntity::class);
    }

    public function findOneByJobKey(string $jobKey): ?SearchReindexJobEntity
    {
        return $this->findOneBy(['jobKey' => $jobKey]);
    }

    public function findOpenByIdempotencyKey(string $idempotencyKey): ?SearchReindexJobEntity
    {
        /** @var SearchReindexJobEntity|null $job */
        $job = $this->createQueryBuilder('job')
            ->andWhere('job.idempotencyKey = :idempotencyKey')
            ->andWhere('job.status IN (:openStatuses)')
            ->setParameter('idempotencyKey', $idempotencyKey)
            ->setParameter('openStatuses', ['requested', 'queued', 'running'])
            ->orderBy('job.createdAt', 'DESC')
            ->addOrderBy('job.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $job;
    }

    /**
     * @return list<SearchReindexJobEntity>
     */
    public function findByCriteria(SearchReindexJobCriteria $criteria): array
    {
        $qb = $this->createQueryBuilder('job');
        $this->applyCriteria($qb, $criteria);

        /** @var list<SearchReindexJobEntity> $jobs */
        $jobs = $qb
            ->orderBy('job.createdAt', 'DESC')
            ->addOrderBy('job.id', 'DESC')
            ->setMaxResults($criteria->limit)
            ->setFirstResult($criteria->offset)
            ->getQuery()
            ->getResult();

        return $jobs;
    }

    public function countByCriteria(SearchReindexJobCriteria $criteria): int
    {
        $qb = $this->createQueryBuilder('job')
            ->select('COUNT(job.id)');
        $this->applyCriteria($qb, $criteria);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function applyCriteria(QueryBuilder $qb, SearchReindexJobCriteria $criteria): void
    {
        if (null !== $criteria->jobKey) {
            $qb->andWhere('job.jobKey = :jobKey')
                ->setParameter('jobKey', $criteria->jobKey);
        }

        if (null !== $criteria->component) {
            $qb->andWhere('job.component = :component')
                ->setParameter('component', $criteria->component);
        }

        if (null !== $criteria->resourceType) {
            $qb->andWhere('job.resourceType = :resourceType')
                ->setParameter('resourceType', $criteria->resourceType);
        }

        if (null !== $criteria->status) {
            $qb->andWhere('job.status = :status')
                ->setParameter('status', $criteria->status);
        }

        if (null !== $criteria->requestedBy) {
            $qb->andWhere('job.requestedBy = :requestedBy')
                ->setParameter('requestedBy', $criteria->requestedBy);
        }

        if (null !== $criteria->createdFrom) {
            $qb->andWhere('job.createdAt >= :createdFrom')
                ->setParameter('createdFrom', $criteria->createdFrom);
        }

        if (null !== $criteria->createdTo) {
            $qb->andWhere('job.createdAt <= :createdTo')
                ->setParameter('createdTo', $criteria->createdTo);
        }
    }
}
