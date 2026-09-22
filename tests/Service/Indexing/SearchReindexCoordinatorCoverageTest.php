<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service\Indexing;

use App\Searching\Contract\Indexing\SearchDocumentIndexerInterface;
use App\Searching\Contract\Indexing\SearchReindexJobTrackerInterface;
use App\Searching\Contract\Producer\SearchableDocumentProviderInterface;
use App\Searching\Contract\Registry\SearchableResourceRegistryInterface;
use App\Searching\Entity\SearchReindexJobEntity;
use App\Searching\Service\Indexing\SearchReindexCoordinator;
use App\Searching\ValueObject\Document\SearchDocument;
use PHPUnit\Framework\TestCase;

final class SearchReindexCoordinatorCoverageTest extends TestCase
{
    public function testRequestReindexReturnsTrackerJobKey(): void
    {
        $tracker = $this->createMock(SearchReindexJobTrackerInterface::class);
        $tracker->expects(self::once())
            ->method('request')
            ->with('cataloging', 'product', 'ops', self::isInstanceOf(\DateTimeImmutable::class))
            ->willReturn(new SearchReindexJobEntity('job-requested'));

        $coordinator = new SearchReindexCoordinator(
            $this->createMock(SearchableResourceRegistryInterface::class),
            $this->createMock(SearchDocumentIndexerInterface::class),
            $tracker,
        );

        $jobId = $coordinator->requestReindex(
            'cataloging',
            'product',
            'ops',
            new \DateTimeImmutable('2026-09-01T00:00:00+00:00'),
        );

        self::assertSame('job-requested', $jobId);
    }

    public function testProviderFailureIsRecordedAndReturnedAsFailedResult(): void
    {
        $provider = $this->createMock(SearchableDocumentProviderInterface::class);
        $provider->method('getSearchableComponentName')->willReturn('cataloging');
        $provider->method('getSearchableResourceName')->willReturn('product');
        $provider->method('provideSearchDocuments')->willThrowException(new \RuntimeException('producer unavailable'));

        $registry = $this->createMock(SearchableResourceRegistryInterface::class);
        $registry->expects(self::once())
            ->method('matching')
            ->with('cataloging', 'product')
            ->willReturn([$provider]);

        $indexer = $this->createMock(SearchDocumentIndexerInterface::class);
        $indexer->expects(self::never())->method('index');

        $tracker = $this->createMock(SearchReindexJobTrackerInterface::class);
        $tracker->expects(self::once())->method('markRunning')->with('job-42', 1);
        $tracker->expects(self::once())
            ->method('markCompleted')
            ->with(
                'job-42',
                1,
                0,
                1,
                ['cataloging:product failed: producer unavailable'],
            );

        $coordinator = new SearchReindexCoordinator($registry, $indexer, $tracker);
        $result = $coordinator->reindexExistingJob('job-42', 'cataloging', 'product');

        self::assertFalse($result->isSuccessful());
        self::assertSame(1, $result->providerCount);
        self::assertSame(0, $result->documentCount);
        self::assertSame(1, $result->failedCount);
        self::assertSame(['cataloging:product failed: producer unavailable'], $result->errors);
    }

    public function testExistingJobIndexesAllProvidedDocumentsAndMarksCompletion(): void
    {
        $document = new SearchDocument(
            component: 'cataloging',
            resourceType: 'product',
            resourceId: '42',
            title: 'Product 42',
            summary: null,
            body: null,
            keywords: [],
            facets: [],
            permissions: [],
            locale: 'en',
            vendorId: null,
            ownerId: null,
            routeName: 'catalog_product_show',
            routeParameters: ['id' => 42],
            updatedAt: new \DateTimeImmutable('2026-09-01T00:00:00+00:00'),
        );
        $provider = $this->createMock(SearchableDocumentProviderInterface::class);
        $provider->method('provideSearchDocuments')->willReturn([$document, $document]);

        $registry = $this->createMock(SearchableResourceRegistryInterface::class);
        $registry->method('matching')->willReturn([$provider]);

        $indexer = $this->createMock(SearchDocumentIndexerInterface::class);
        $indexer->expects(self::exactly(2))->method('index')->with($document);

        $tracker = $this->createMock(SearchReindexJobTrackerInterface::class);
        $tracker->expects(self::once())->method('markRunning')->with('job-success', 1);
        $tracker->expects(self::once())->method('markCompleted')->with('job-success', 1, 2, 0, []);

        $result = (new SearchReindexCoordinator($registry, $indexer, $tracker))
            ->reindexExistingJob('job-success');

        self::assertTrue($result->isSuccessful());
        self::assertSame(2, $result->documentCount);
    }
}
