<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service\Coverage;

use App\Searching\Entity\SearchIndexEntity;
use App\Searching\Entity\SearchReindexJobEntity;
use App\Searching\Entity\SearchRelevanceProfileEntity;
use App\Searching\Entity\SearchSynonymEntity;
use App\Searching\Service\Indexing\SearchNullIndexedResourceTracker;
use App\Searching\Service\Indexing\SearchNullReindexJobTracker;
use App\Searching\Service\Serialization\SearchIndexSerializer;
use App\Searching\Service\Serialization\SearchReindexJobSerializer;
use App\Searching\Service\Serialization\SearchRelevanceProfileSerializer;
use App\Searching\Service\Serialization\SearchSynonymSerializer;
use App\Searching\Value\Health\SearchHealthIndicator;
use App\Searching\Value\Indexing\SearchDocumentFingerprint;
use App\Searching\Value\Indexing\SearchIndexedResourceState;
use App\Searching\Value\Indexing\SearchIndexLifecycleRegistrySyncResult;
use PHPUnit\Framework\TestCase;

final class SearchRuntimeValueCoverageTest extends TestCase
{
    public function testIndexingValueSemantics(): void
    {
        $sourceUpdatedAt = new \DateTimeImmutable('2026-09-15T12:00:00+00:00');
        $fingerprint = new SearchDocumentFingerprint('ordering', 'order', '42', 'hash-42', $sourceUpdatedAt);

        self::assertTrue($fingerprint->matches('hash-42', new \DateTimeImmutable('2026-09-15T12:00:00+00:00')));
        self::assertFalse($fingerprint->matches('other', $sourceUpdatedAt));
        self::assertFalse($fingerprint->matches('hash-42', null));

        $state = new SearchIndexedResourceState(
            component: 'ordering',
            resourceType: 'order',
            resourceId: '42',
            documentHash: 'hash-42',
            indexedAt: new \DateTimeImmutable('2026-09-15T12:01:00+00:00'),
            sourceUpdatedAt: $sourceUpdatedAt,
            status: 'indexed',
        );

        self::assertTrue($state->isCurrent($fingerprint));
        self::assertFalse((new SearchIndexedResourceState(
            component: 'ordering',
            resourceType: 'order',
            resourceId: '42',
            documentHash: 'hash-42',
            indexedAt: null,
            sourceUpdatedAt: $sourceUpdatedAt,
            status: 'failed',
        ))->isCurrent($fingerprint));

        self::assertSame(
            ['synced' => true, 'status' => 'ready', 'reason' => null],
            SearchIndexLifecycleRegistrySyncResult::synced('ready')->toArray(),
        );
        self::assertSame(
            ['synced' => false, 'status' => 'skipped', 'reason' => 'registry unavailable'],
            SearchIndexLifecycleRegistrySyncResult::skipped('registry unavailable')->toArray(),
        );
    }

    public function testHealthIndicatorSemantics(): void
    {
        $healthy = new SearchHealthIndicator('provider', 'healthy', 'Ready');
        $unhealthy = new SearchHealthIndicator('provider', 'unhealthy', 'Unavailable');

        self::assertTrue($healthy->isHealthy());
        self::assertFalse($healthy->isUnhealthy());
        self::assertFalse($unhealthy->isHealthy());
        self::assertTrue($unhealthy->isUnhealthy());
    }

    public function testNullIndexedResourceTrackerTransitions(): void
    {
        $tracker = new SearchNullIndexedResourceTracker();
        $sourceUpdatedAt = new \DateTimeImmutable('2026-09-15T12:00:00+00:00');
        $fingerprint = new SearchDocumentFingerprint('ordering', 'order', '42', 'hash-42', $sourceUpdatedAt);

        self::assertNull($tracker->find('ordering', 'order', '42'));
        self::assertFalse($tracker->isCurrent($fingerprint));

        $indexed = $tracker->markIndexed($fingerprint);
        self::assertSame('indexed', $indexed->status);
        self::assertInstanceOf(\DateTimeImmutable::class, $indexed->indexedAt);

        self::assertSame('unchanged', $tracker->markUnchanged($fingerprint)->status);
        self::assertSame('boom', $tracker->markFailed($fingerprint, 'boom')->errorMessage);

        $removed = $tracker->markRemoved('ordering', 'order', '42');
        self::assertSame('removed', $removed->status);
        self::assertNull($removed->documentHash);
    }

    public function testNullReindexJobTrackerContract(): void
    {
        $tracker = new SearchNullReindexJobTracker();
        $job = $tracker->request('ordering', 'order', 'vendor-1', new \DateTimeImmutable('2026-09-15T00:00:00+00:00'), 'idem', 'sync');

        self::assertSame('ordering', $job->getComponent());
        self::assertSame('order', $job->getResourceType());
        self::assertSame(32, strlen($job->getJobKey()));

        $tracker->markQueued($job->getJobKey(), 'sync', 'idem');
        $tracker->markDispatchFailed($job->getJobKey(), 'dispatch failed');
        $tracker->markRunning($job->getJobKey(), 1);
        $tracker->markCompleted($job->getJobKey(), 1, 2, 0);
        $tracker->markFailed($job->getJobKey(), 1, 1, 1, ['failed']);

        self::addToAssertionCount(5);
    }

    public function testSerializerListMethods(): void
    {
        $index = new SearchIndexEntity('Orders', 'opensearch', 'sr_ordering_order', 'ordering', 'order');
        self::assertSame('Orders', (new SearchIndexSerializer())->serializeList([$index])[0]['nameEntity']);

        $job = new SearchReindexJobEntity('job-1', 'ordering', 'order', 'vendor-1');
        $job->markQueued('sync', 'idem');
        self::assertSame('job-1', (new SearchReindexJobSerializer())->serializeList([$job])[0]['jobKey']);

        $profile = SearchRelevanceProfileEntity::create('Orders', ['title' => 2], 'ordering', 'order');
        self::assertSame('Orders', (new SearchRelevanceProfileSerializer())->serializeProfiles([$profile])[0]['nameEntity']);

        $synonym = SearchSynonymEntity::create('bill', ['invoice'], 'en');
        self::assertSame('bill', (new SearchSynonymSerializer())->serializeSynonyms([$synonym])[0]['sourceTerm']);
    }
}
