<?php

declare(strict_types=1);

namespace App\Searching\Tests\Entity;

use App\Searching\Entity\SearchIndexedResourceEntity;
use App\Searching\Entity\SearchIndexEntity;
use App\Searching\Entity\SearchReindexJobEntity;
use App\Searching\Value\Observability\SearchExecutionContext;
use PHPUnit\Framework\TestCase;

final class SearchLifecycleEntityTest extends TestCase
{
    public function testSearchIndexLifecycleStateIsObservable(): void
    {
        $entity = new SearchIndexEntity('Products', 'opensearch', 'sr_products', 'cataloging', 'product');

        self::assertNull($entity->getId());
        self::assertSame('Products', $entity->getName());
        self::assertSame('opensearch', $entity->getProvider());
        self::assertSame('sr_products', $entity->getIndexName());
        self::assertSame('cataloging', $entity->getComponent());
        self::assertSame('product', $entity->getResourceType());
        self::assertTrue($entity->isEnabled());
        self::assertSame('registered', $entity->getLifecycleStatus());
        self::assertNull($entity->getLastLifecycleOperation());
        self::assertNull($entity->getLastLifecycleAt());
        self::assertNull($entity->getLastLifecycleError());
        self::assertNull($entity->getLastIndexedAt());
        self::assertInstanceOf(\DateTimeImmutable::class, $entity->getCreatedAt());
        self::assertInstanceOf(\DateTimeImmutable::class, $entity->getUpdatedAt());

        $indexedAt = new \DateTimeImmutable('2026-09-16T12:00:00+00:00');
        $entity->rename('Catalog Products');
        $entity->updateIndexName('sr_catalog_products');
        $entity->setEnabled(false);
        $entity->markIndexed($indexedAt);
        $entity->markLifecycleResult('ensure', 'ready', 'diagnostic');

        self::assertSame('Catalog Products', $entity->getName());
        self::assertSame('sr_catalog_products', $entity->getIndexName());
        self::assertFalse($entity->isEnabled());
        self::assertSame($indexedAt, $entity->getLastIndexedAt());
        self::assertSame('ensure', $entity->getLastLifecycleOperation());
        self::assertSame('ready', $entity->getLifecycleStatus());
        self::assertSame('diagnostic', $entity->getLastLifecycleError());
        self::assertInstanceOf(\DateTimeImmutable::class, $entity->getLastLifecycleAt());

        $entity->apply('Final Products', 'sr_final_products', true);
        self::assertSame('Final Products', $entity->getName());
        self::assertSame('sr_final_products', $entity->getIndexName());
        self::assertTrue($entity->isEnabled());
    }

    public function testIndexedResourceLifecycleTransitionsPreserveSourceState(): void
    {
        $entity = new SearchIndexedResourceEntity('cataloging', 'product', '42');
        $sourceUpdatedAt = new \DateTimeImmutable('2026-09-16T11:30:00+00:00');

        self::assertNull($entity->getId());
        self::assertSame('cataloging', $entity->getComponent());
        self::assertSame('product', $entity->getResourceType());
        self::assertSame('42', $entity->getResourceId());
        self::assertNull($entity->getDocumentHash());
        self::assertNull($entity->getIndexedAt());
        self::assertNull($entity->getSourceUpdatedAt());
        self::assertSame('pending', $entity->getStatus());
        self::assertNull($entity->getErrorMessage());
        self::assertInstanceOf(\DateTimeImmutable::class, $entity->getCreatedAt());
        self::assertInstanceOf(\DateTimeImmutable::class, $entity->getUpdatedAt());

        $entity->markIndexed('hash-1', $sourceUpdatedAt);
        self::assertSame('hash-1', $entity->getDocumentHash());
        self::assertSame($sourceUpdatedAt, $entity->getSourceUpdatedAt());
        self::assertSame('indexed', $entity->getStatus());
        self::assertInstanceOf(\DateTimeImmutable::class, $entity->getIndexedAt());

        $entity->markUnchanged('hash-2', $sourceUpdatedAt);
        self::assertSame('unchanged', $entity->getStatus());
        self::assertSame('hash-2', $entity->getDocumentHash());

        $entity->markFailed('hash-3', $sourceUpdatedAt, 'backend unavailable');
        self::assertSame('failed', $entity->getStatus());
        self::assertSame('backend unavailable', $entity->getErrorMessage());

        $entity->markRemoved();
        self::assertSame('removed', $entity->getStatus());
        self::assertNull($entity->getErrorMessage());
    }

    public function testReindexJobLifecycleTracksContextDispatchAndCompletion(): void
    {
        $changedSince = new \DateTimeImmutable('2026-09-15T00:00:00+00:00');
        $context = SearchExecutionContext::create('corr-17', 'req-17', 'administering', 'search.reindex', 'admin-1', ['reason' => 'rc']);
        $entity = new SearchReindexJobEntity('job-17', 'cataloging', 'product', 'admin-1', $changedSince, 'idem-17', 'messenger', $context);

        self::assertNull($entity->getId());
        self::assertSame('job-17', $entity->getJobKey());
        self::assertSame('cataloging', $entity->getComponent());
        self::assertSame('product', $entity->getResourceType());
        self::assertSame('requested', $entity->getStatus());
        self::assertSame('admin-1', $entity->getRequestedBy());
        self::assertSame($changedSince, $entity->getChangedSince());
        self::assertSame('idem-17', $entity->getIdempotencyKey());
        self::assertSame('messenger', $entity->getDispatchMode());
        self::assertSame('corr-17', $entity->getCorrelationId());
        self::assertSame('req-17', $entity->getRequestId());
        self::assertSame('administering', $entity->getSourceComponent());
        self::assertSame('search.reindex', $entity->getSourceOperation());
        $executionContext = $entity->getExecutionContext();
        $metadata = $executionContext['metadata'];
        self::assertIsArray($metadata);
        self::assertSame('rc', $metadata['reason']);
        self::assertNull($entity->getQueuedAt());
        self::assertSame(0, $entity->getMessageAttempts());
        self::assertNull($entity->getLastMessageFailureAt());
        self::assertNull($entity->getStartedAt());
        self::assertNull($entity->getFinishedAt());
        self::assertSame(0, $entity->getProviderCount());
        self::assertSame(0, $entity->getProcessedCount());
        self::assertSame(0, $entity->getFailedCount());
        self::assertSame([], $entity->getErrors());
        self::assertNull($entity->getErrorMessage());
        self::assertInstanceOf(\DateTimeImmutable::class, $entity->getCreatedAt());
        self::assertInstanceOf(\DateTimeImmutable::class, $entity->getUpdatedAt());

        $entity->markQueued('messenger', 'idem-17');
        self::assertSame('queued', $entity->getStatus());
        self::assertInstanceOf(\DateTimeImmutable::class, $entity->getQueuedAt());

        $entity->markRunning(2);
        self::assertSame('running', $entity->getStatus());
        self::assertSame(1, $entity->getMessageAttempts());
        self::assertSame(2, $entity->getProviderCount());
        self::assertInstanceOf(\DateTimeImmutable::class, $entity->getStartedAt());

        $entity->markCompleted(2, 8, 1, ['one stale source']);
        self::assertSame('completed_with_errors', $entity->getStatus());
        self::assertSame(8, $entity->getProcessedCount());
        self::assertSame(1, $entity->getFailedCount());
        self::assertSame(['one stale source'], $entity->getErrors());
        self::assertSame('one stale source', $entity->getErrorMessage());
        self::assertInstanceOf(\DateTimeImmutable::class, $entity->getFinishedAt());

        $entity->markDispatchFailed('transport down');
        self::assertSame('failed', $entity->getStatus());
        self::assertSame(2, $entity->getMessageAttempts());
        self::assertSame('transport down', $entity->getErrorMessage());
        self::assertSame(['transport down'], $entity->getErrors());
        self::assertInstanceOf(\DateTimeImmutable::class, $entity->getLastMessageFailureAt());

        $entity->markFailed(2, 4, 0, []);
        self::assertSame('failed', $entity->getStatus());
        self::assertSame(1, $entity->getFailedCount());
        self::assertSame('Reindex failed.', $entity->getErrorMessage());
    }
}
