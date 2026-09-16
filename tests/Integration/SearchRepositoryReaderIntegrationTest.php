<?php

declare(strict_types=1);

namespace App\Searching\Tests\Integration;

use App\Searching\Entity\SearchIndexedResourceEntity;
use App\Searching\Entity\SearchIndexEntity;
use App\Searching\Entity\SearchQueryLogEntity;
use App\Searching\Entity\SearchReindexJobEntity;
use App\Searching\Entity\SearchRelevanceProfileEntity;
use App\Searching\Entity\SearchSynonymEntity;
use App\Searching\Repository\SearchIndexedResourceRepository;
use App\Searching\Repository\SearchIndexRepository;
use App\Searching\Repository\SearchQueryLogRepository;
use App\Searching\Repository\SearchReindexJobRepository;
use App\Searching\Repository\SearchRelevanceProfileRepository;
use App\Searching\Repository\SearchSynonymRepository;
use App\Searching\Service\Indexing\SearchIndexedResourceReader;
use App\Searching\Service\Indexing\SearchIndexReader;
use App\Searching\Service\Indexing\SearchReindexJobReader;
use App\Searching\Service\Query\SearchQueryLogReader;
use App\Searching\Service\Tuning\SearchRelevanceProfileReader;
use App\Searching\Service\Tuning\SearchSynonymReader;
use App\Searching\Value\Indexing\SearchIndexCriteria;
use App\Searching\Value\Indexing\SearchIndexedResourceCriteria;
use App\Searching\Value\Indexing\SearchReindexJobCriteria;
use App\Searching\Value\Observability\SearchExecutionContext;
use App\Searching\Value\Query\SearchQueryExecutionTrace;
use App\Searching\Value\Query\SearchQueryLogCriteria;
use App\Searching\Value\Tuning\SearchRelevanceProfileCriteria;
use App\Searching\Value\Tuning\SearchSynonymCriteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class SearchRepositoryReaderIntegrationTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private ManagerRegistry $registry;

    protected static function getKernelClass(): string
    {
        return \App\Searching\Kernel::class;
    }

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $registry = $kernel->getContainer()->get('doctrine');
        self::assertInstanceOf(ManagerRegistry::class, $registry);
        $this->registry = $registry;
        $entityManager = $registry->getManager();
        self::assertInstanceOf(EntityManagerInterface::class, $entityManager);
        $this->entityManager = $entityManager;
        $this->entityManager->beginTransaction();
    }

    protected function tearDown(): void
    {
        if ($this->entityManager->getConnection()->isTransactionActive()) {
            $this->entityManager->rollback();
        }
        $this->entityManager->clear();
        parent::tearDown();
    }

    public function testIndexAndIndexedResourceRepositoriesAndReaders(): void
    {
        $registry = $this->registry;

        $indexRepository = new SearchIndexRepository($registry);
        $indexReader = new SearchIndexReader($indexRepository);
        $index = new SearchIndexEntity('Wave6 Orders', 'null', 'wave6_orders', 'wave6', 'order');
        $this->entityManager->persist($index);

        $resourceRepository = new SearchIndexedResourceRepository($registry);
        $resourceReader = new SearchIndexedResourceReader($resourceRepository);
        $resource = new SearchIndexedResourceEntity('wave6', 'order', '42');
        $resource->markIndexed('hash-42', new \DateTimeImmutable('2026-09-15T12:00:00+00:00'));
        $this->entityManager->persist($resource);
        $this->entityManager->flush();

        $criteria = new SearchIndexCriteria(provider: 'null', component: 'wave6', resourceType: 'order', enabled: true);
        self::assertSame([$index], $indexReader->list($criteria));
        self::assertSame(1, $indexReader->count($criteria));
        self::assertSame($index, $indexReader->findOne('null', 'wave6', 'order'));
        self::assertSame($index, $indexRepository->getOrCreate('null', 'wave6', 'order', 'ignored', 'ignored'));
        self::assertInstanceOf(SearchIndexEntity::class, $indexRepository->getOrCreate('null', 'wave6', 'invoice', 'Wave6 Invoices', 'wave6_invoices'));

        $resourceCriteria = new SearchIndexedResourceCriteria(component: 'wave6', resourceType: 'order', resourceId: '42', status: 'indexed');
        self::assertSame([$resource], $resourceReader->list($resourceCriteria));
        self::assertSame(1, $resourceReader->count($resourceCriteria));
        self::assertSame($resource, $resourceRepository->findOneByIdentity('wave6', 'order', '42'));
        self::assertSame($resource, $resourceRepository->getOrCreate('wave6', 'order', '42'));
        self::assertInstanceOf(SearchIndexedResourceEntity::class, $resourceRepository->getOrCreate('wave6', 'order', '43'));
        self::assertSame([$resource], $resourceRepository->findStaleSince(new \DateTimeImmutable('2099-01-01T00:00:00+00:00')));
    }

    public function testReindexProfileAndSynonymRepositoriesAndReaders(): void
    {
        $registry = $this->registry;

        $jobRepository = new SearchReindexJobRepository($registry);
        $jobReader = new SearchReindexJobReader($jobRepository);
        $job = new SearchReindexJobEntity('wave6-job', 'wave6', 'order', 'tester', null, 'wave6-idem', 'sync');
        $job->markQueued('sync', 'wave6-idem');
        $this->entityManager->persist($job);

        $profileRepository = new SearchRelevanceProfileRepository($registry);
        $profileReader = new SearchRelevanceProfileReader($profileRepository);
        $profile = SearchRelevanceProfileEntity::create('Wave6 Profile', ['title' => 2], 'wave6', 'order');
        $this->entityManager->persist($profile);

        $synonymRepository = new SearchSynonymRepository($registry);
        $synonymReader = new SearchSynonymReader($synonymRepository);
        $synonym = SearchSynonymEntity::create('bill', ['invoice'], 'en');
        $this->entityManager->persist($synonym);
        $this->entityManager->flush();

        $jobCriteria = new SearchReindexJobCriteria(jobKey: 'wave6-job', component: 'wave6', resourceType: 'order', status: 'queued', requestedBy: 'tester');
        self::assertSame([$job], $jobReader->list($jobCriteria));
        self::assertSame(1, $jobReader->count($jobCriteria));
        self::assertSame($job, $jobReader->findOne('wave6-job'));
        self::assertSame($job, $jobRepository->findOneByJobKey('wave6-job'));
        self::assertSame($job, $jobRepository->findOpenByIdempotencyKey('wave6-idem'));

        $profileCriteria = new SearchRelevanceProfileCriteria(nameEntity: 'Wave6', component: 'wave6', resourceType: 'order', enabled: true);
        self::assertSame([$profile], $profileReader->find($profileCriteria));
        self::assertSame(1, $profileReader->count($profileCriteria));
        self::assertSame($profile, $profileReader->findOne((int) $profile->getId()));

        $synonymCriteria = new SearchSynonymCriteria(locale: 'en', sourceTerm: 'bill', enabled: true);
        self::assertSame([$synonym], $synonymReader->find($synonymCriteria));
        self::assertSame(1, $synonymReader->count($synonymCriteria));
        self::assertSame($synonym, $synonymReader->findOne((int) $synonym->getId()));
    }

    public function testQueryLogRepositoryAndReaderApplyAllCriteria(): void
    {
        $repository = new SearchQueryLogRepository($this->registry);
        $reader = new SearchQueryLogReader($repository);
        $context = new SearchExecutionContext(
            correlationId: 'wave6-correlation',
            requestId: 'wave6-request',
            sourceComponent: 'ordering',
            sourceOperation: 'search',
            actorId: 'user-42',
        );
        $log = SearchQueryLogEntity::fromTrace(new SearchQueryExecutionTrace(
            query: 'wave6 invoice',
            userId: 'user-42',
            tenantId: 'tenant-42',
            providerName: 'null',
            providerTotal: 3,
            returnedTotal: 2,
            deniedCount: 1,
            durationMs: 12.5,
            executedAt: new \DateTimeImmutable('2026-09-15T12:00:00+00:00'),
            successful: true,
            executionContext: $context,
        ));
        $this->entityManager->persist($log);
        $this->entityManager->flush();

        $criteria = new SearchQueryLogCriteria(
            limit: 10,
            query: 'invoice',
            userId: 'user-42',
            tenantId: 'tenant-42',
            providerName: 'null',
            correlationId: 'wave6-correlation',
            requestId: 'wave6-request',
            sourceComponent: 'ordering',
            sourceOperation: 'search',
            successful: true,
            from: new \DateTimeImmutable('2026-09-15T11:00:00+00:00'),
            to: new \DateTimeImmutable('2026-09-15T13:00:00+00:00'),
        );

        self::assertSame([$log], $reader->recent($criteria));
        self::assertSame(1, $reader->count($criteria));
    }
}
