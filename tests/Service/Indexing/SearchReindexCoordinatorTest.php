<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service\Indexing;

use App\Searching\Contract\Indexing\SearchReindexJobTrackerInterface;
use App\Searching\Contract\Producer\SearchableDocumentProviderInterface;
use App\Searching\Entity\SearchReindexJobEntity;
use App\Searching\Provider\Backend\SearchNullProvider;
use App\Searching\Service\Indexing\SearchDocumentIndexer;
use App\Searching\Service\Indexing\SearchReindexCoordinator;
use App\Searching\Service\Registry\SearchableResourceRegistry;
use App\Searching\Tests\Fixture\FakeSearchableDocumentProvider;
use PHPUnit\Framework\TestCase;

final class SearchReindexCoordinatorTest extends TestCase
{
    public function testItReindexesMatchingProviders(): void
    {
        $registry = new SearchableResourceRegistry();
        $registry->add(new FakeSearchableDocumentProvider('cataloging', 'product'));
        $registry->add(new FakeSearchableDocumentProvider('messaging', 'message'));

        $coordinator = new SearchReindexCoordinator(
            $registry,
            new SearchDocumentIndexer(new SearchNullProvider()),
        );

        $result = $coordinator->reindex('cataloging', 'product');

        self::assertTrue($result->isSuccessful());
        self::assertSame(1, $result->providerCount);
        self::assertSame(1, $result->documentCount);
    }

    public function testRequestReindexReturnsTrackedJobKey(): void
    {
        $tracker = $this->createMock(SearchReindexJobTrackerInterface::class);
        $tracker->expects(self::once())
            ->method('request')
            ->with('cataloging', 'product', 'operator', self::isInstanceOf(\DateTimeImmutable::class))
            ->willReturn(new SearchReindexJobEntity('job-42'));

        $coordinator = new SearchReindexCoordinator(
            new SearchableResourceRegistry(),
            new SearchDocumentIndexer(new SearchNullProvider()),
            $tracker,
        );

        self::assertSame('job-42', $coordinator->requestReindex('cataloging', 'product', 'operator', new \DateTimeImmutable('2026-09-15T12:00:00+00:00')));
    }

    public function testProviderFailureIsRecordedWithoutAbortingOtherProviders(): void
    {
        $registry = new SearchableResourceRegistry();
        $registry->add(new FailingSearchableDocumentProvider('cataloging', 'broken'));
        $registry->add(new FakeSearchableDocumentProvider('cataloging', 'product'));

        $coordinator = new SearchReindexCoordinator(
            $registry,
            new SearchDocumentIndexer(new SearchNullProvider()),
        );

        $result = $coordinator->reindex('cataloging');

        self::assertFalse($result->isSuccessful());
        self::assertSame(2, $result->providerCount);
        self::assertSame(1, $result->documentCount);
        self::assertSame(1, $result->failedCount);
        self::assertCount(1, $result->errors);
        self::assertStringContainsString('cataloging:broken failed: provider exploded', $result->errors[0]);
    }
}

final class FailingSearchableDocumentProvider implements SearchableDocumentProviderInterface
{
    public function __construct(
        private readonly string $component,
        private readonly string $resource,
    ) {
    }

    public function getSearchableResourceName(): string
    {
        return $this->resource;
    }

    public function getSearchableComponentName(): string
    {
        return $this->component;
    }

    public function provideSearchDocuments(?\DateTimeImmutable $changedSince = null): iterable
    {
        unset($changedSince);

        if ('' !== $this->component) {
            throw new \RuntimeException('provider exploded');
        }

        return [];
    }
}
