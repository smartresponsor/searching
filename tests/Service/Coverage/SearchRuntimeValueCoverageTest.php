<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service\Coverage;

use App\Searching\Builder\Provider\SearchIndexMappingBuilder;
use App\Searching\Builder\Provider\SearchIndexNameBuilder;
use App\Searching\Entity\SearchIndexEntity;
use App\Searching\Entity\SearchQueryLogEntity;
use App\Searching\Entity\SearchReindexJobEntity;
use App\Searching\Entity\SearchRelevanceProfileEntity;
use App\Searching\Entity\SearchSynonymEntity;
use App\Searching\Resolver\Tuning\SearchNullQueryTuningResolver;
use App\Searching\Service\Indexing\SearchNullIndexedResourceTracker;
use App\Searching\Service\Indexing\SearchNullReindexJobTracker;
use App\Searching\Service\Provider\SearchQueryPayloadMapper;
use App\Searching\Service\Serialization\SearchIndexSerializer;
use App\Searching\Service\Serialization\SearchReindexJobSerializer;
use App\Searching\Service\Serialization\SearchRelevanceProfileSerializer;
use App\Searching\Service\Serialization\SearchSynonymSerializer;
use App\Searching\Value\Document\SearchDocument;
use App\Searching\Value\Flow\SearchOperationLimitRequest;
use App\Searching\Value\Health\SearchHealthIndicator;
use App\Searching\Value\Indexing\SearchDocumentFingerprint;
use App\Searching\Value\Indexing\SearchIndexedResourceState;
use App\Searching\Value\Indexing\SearchIndexLifecycleRegistrySyncResult;
use App\Searching\Value\Provider\SearchBulkOperation;
use App\Searching\Value\Provider\SearchBulkOperationSet;
use App\Searching\Value\Provider\SearchProviderConfiguration;
use App\Searching\Value\Query\SearchQuery;
use App\Searching\Value\Query\SearchQueryExecutionTrace;
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
        self::assertNull($profile->getId());
        self::assertSame('Orders', $profile->getName());
        self::assertSame('ordering', $profile->getComponent());
        self::assertSame('order', $profile->getResourceType());
        self::assertSame(['title' => 2], $profile->getFieldWeights());
        self::assertTrue($profile->isEnabled());
        self::assertInstanceOf(\DateTimeImmutable::class, $profile->getCreatedAt());
        self::assertInstanceOf(\DateTimeImmutable::class, $profile->getUpdatedAt());
        $profile->update(' Global ', [' ' => 9, ' score ' => 1.5], ' ', ' ', false);
        self::assertSame('Global', $profile->getName());
        self::assertSame(['score' => 1.5], $profile->getFieldWeights());
        self::assertNull($profile->getComponent());
        self::assertNull($profile->getResourceType());
        self::assertFalse($profile->isEnabled());
        self::assertSame('Global', (new SearchRelevanceProfileSerializer())->serializeProfiles([$profile])[0]['nameEntity']);

        $synonym = SearchSynonymEntity::create('bill', ['invoice'], 'en');
        self::assertNull($synonym->getId());
        self::assertSame('bill', (new SearchSynonymSerializer())->serializeSynonyms([$synonym])[0]['sourceTerm']);
    }

    public function testQueryLogEntityExposesCompleteTraceProjection(): void
    {
        $executedAt = new \DateTimeImmutable('2026-09-16T12:30:00+00:00');
        $log = SearchQueryLogEntity::fromTrace(new SearchQueryExecutionTrace(
            query: 'needle',
            userId: 'user-1',
            vendorId: 'vendor-1',
            providerName: 'elastic',
            providerTotal: 5,
            returnedTotal: 3,
            deniedCount: 2,
            durationMs: 4.5,
            executedAt: $executedAt,
            successful: false,
            errorClass: \RuntimeException::class,
            errorMessage: 'backend failed',
        ));

        self::assertNull($log->getId());
        self::assertSame('needle', $log->getQueryText());
        self::assertSame('user-1', $log->getUserId());
        self::assertSame('vendor-1', $log->getVendorId());
        self::assertNull($log->getCorrelationId());
        self::assertNull($log->getRequestId());
        self::assertNull($log->getSourceComponent());
        self::assertNull($log->getSourceOperation());
        self::assertSame('elastic', $log->getProviderName());
        self::assertSame(5, $log->getProviderTotal());
        self::assertSame(3, $log->getReturnedTotal());
        self::assertSame(2, $log->getDeniedCount());
        self::assertSame(4.5, $log->getDurationMs());
        self::assertFalse($log->isSuccessful());
        self::assertSame(\RuntimeException::class, $log->getErrorClass());
        self::assertSame('backend failed', $log->getErrorMessage());
        self::assertSame($executedAt, $log->getCreatedAt());
        self::assertSame($executedAt, $log->getUpdatedAt());
        self::assertSame([], $log->getMetadata()['executionContext']);
    }

    public function testProviderNeutralQueryPayloadAndNullTuning(): void
    {
        $payload = (new SearchQueryPayloadMapper())->map(new SearchQuery(
            query: 'needle',
            components: ['ordering'],
            resourceTypes: ['order'],
            filters: ['status' => 'open'],
            sort: ['updatedAt' => 'desc'],
            page: 3,
            limit: 10,
            locale: 'en_US',
            vendorId: 'vendor-1',
            userId: 'user-1',
            userPermissions: ['order.view'],
            includeHighlights: false,
            includeFacets: true,
        ));

        self::assertSame(20, $payload['offset']);
        self::assertSame(['order.view'], $payload['user_permissions']);
        self::assertFalse($payload['include_highlights']);
        self::assertTrue($payload['include_facets']);

        $tuning = (new SearchNullQueryTuningResolver())->resolve(new SearchQuery('needle'));
        self::assertSame([], $tuning->expandedTerms);
        self::assertSame([], $tuning->fieldWeights);
        self::assertSame([], $tuning->matchedSynonyms);
        self::assertSame([], $tuning->relevanceProfiles);

        $zeroPage = (new SearchQueryPayloadMapper())->map(new SearchQuery('needle', page: 0, limit: 10));
        self::assertSame(0, $zeroPage['offset']);

        $first = new SearchBulkOperation('index', 'orders', '1', ['title' => 'One']);
        $second = new SearchBulkOperation('delete', 'orders', '2');
        self::assertSame('index', $first->toArray()['operation']);
        $set = new SearchBulkOperationSet([$first, $second]);
        self::assertCount(2, $set);
        self::assertCount(2, iterator_to_array($set));
        self::assertSame([$first, $second], $set->groupedByIndex()['orders']);

        $document = new SearchDocument(
            component: 'Ordering API',
            resourceType: 'Order Item',
            resourceId: 'ID 42',
            title: 'Order',
            summary: null,
            body: null,
            keywords: [],
            facets: [],
            permissions: [],
            locale: null,
            vendorId: null,
            ownerId: null,
            routeName: 'order_show',
            routeParameters: ['id' => 'ID 42'],
            updatedAt: new \DateTimeImmutable('2026-09-16T12:00:00+00:00'),
        );
        $nameBuilder = new SearchIndexNameBuilder();
        self::assertSame('sr_ordering_api_order_item', $nameBuilder->buildForParts('sr', 'Ordering API', 'Order Item'));
        self::assertSame('ordering_api_order_item_id_42', $nameBuilder->buildDocumentId($document));

        $mapping = (new SearchIndexMappingBuilder())->build(
            'sr_orders',
            'ordering',
            'order',
            new SearchProviderConfiguration(
                nameEntity: 'elastic',
                enabled: true,
                dsn: null,
                indexPrefix: 'sr',
                options: ['text_analyzer' => '', 'keyword_normalizer' => 123],
            ),
        );
        $titleProperty = $mapping->properties['title'];
        $componentProperty = $mapping->properties['component'];
        self::assertIsArray($titleProperty);
        self::assertIsArray($componentProperty);
        self::assertSame('standard', $titleProperty['analyzer']);
        self::assertSame('lowercase', $componentProperty['normalizer']);

        try {
            new SearchBulkOperation('rotate', 'orders', '3');
            self::fail('Invalid bulk operation must be rejected.');
        } catch (\InvalidArgumentException $exception) {
            self::assertStringContainsString('index', $exception->getMessage());
        }

        $scopedLimit = SearchOperationLimitRequest::forReindexDispatch('ordering', 'order', requestedBy: 'worker-1');
        self::assertSame('worker-1:ordering:order', $scopedLimit->identity);
        self::assertSame(20, $scopedLimit->cost);

        $anonymousLimit = SearchOperationLimitRequest::forReindexDispatch();
        self::assertSame('anonymous', $anonymousLimit->identity);
        self::assertSame(100, $anonymousLimit->cost);
    }
}
