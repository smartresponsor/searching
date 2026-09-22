<?php

declare(strict_types=1);

namespace App\Searching\Tests\Value;

use App\Searching\ValueObject\Indexing\SearchIndexCriteria;
use App\Searching\ValueObject\Indexing\SearchIndexedResourceCriteria;
use App\Searching\ValueObject\Indexing\SearchReindexJobCriteria;
use App\Searching\ValueObject\Provider\SearchProviderConfiguration;
use App\Searching\ValueObject\Query\SearchQueryLogCriteria;
use App\Searching\ValueObject\Tuning\SearchRelevanceProfileCriteria;
use App\Searching\ValueObject\Tuning\SearchSynonymCriteria;
use PHPUnit\Framework\TestCase;

final class SearchCriteriaValueTest extends TestCase
{
    public function testProviderConfigurationNormalizesInputAndSerializesBackendConfiguration(): void
    {
        $configuration = SearchProviderConfiguration::fromArray('elastic', [
            'enabled' => 1,
            'dsn' => 'http://search.internal:9200',
            'index_prefix' => 'tenant',
            'options' => ['timeout' => 2.5],
        ]);

        self::assertSame('elastic', $configuration->nameEntity);
        self::assertTrue($configuration->enabled);
        self::assertSame('http://search.internal:9200', $configuration->dsn);
        self::assertSame('tenant', $configuration->indexPrefix);
        self::assertSame(['timeout' => 2.5], $configuration->options);
        self::assertSame([
            'enabled' => true,
            'dsn' => 'http://search.internal:9200',
            'index_prefix' => 'tenant',
            'options' => ['timeout' => 2.5],
        ], $configuration->toBackendConfiguration());

        $defaults = SearchProviderConfiguration::fromArray('null', [
            'dsn' => '',
            'index_prefix' => '',
            'options' => 'invalid',
        ]);

        self::assertFalse($defaults->enabled);
        self::assertNull($defaults->dsn);
        self::assertSame('sr', $defaults->indexPrefix);
        self::assertSame([], $defaults->options);
    }

    public function testIndexCriteriaNormalizesAliasesBoundsAndInvalidValues(): void
    {
        $criteria = SearchIndexCriteria::fromArray([
            'provider' => ' elastic ',
            'component' => ' billing ',
            'resource' => ' invoice ',
            'enabled' => 'false',
            'limit' => '900',
            'offset' => '12',
        ]);

        self::assertSame('elastic', $criteria->provider);
        self::assertSame('billing', $criteria->component);
        self::assertSame('invoice', $criteria->resourceType);
        self::assertFalse($criteria->enabled);
        self::assertSame(500, $criteria->limit);
        self::assertSame(12, $criteria->offset);

        $fallback = SearchIndexCriteria::fromArray([
            'provider' => [],
            'enabled' => 'not-a-bool',
            'limit' => 0,
            'offset' => -4,
        ], 33);

        self::assertNull($fallback->provider);
        self::assertNull($fallback->enabled);
        self::assertSame(33, $fallback->limit);
        self::assertSame(0, $fallback->offset);
    }

    public function testReindexJobCriteriaNormalizesAliasesDatesAndInvalidInput(): void
    {
        $criteria = SearchReindexJobCriteria::fromArray([
            'job_id' => ' job-42 ',
            'component' => ' billing ',
            'resource_type' => ' invoice ',
            'status' => ' completed ',
            'requested_by' => ' ops ',
            'from' => '2026-09-01T10:00:00+00:00',
            'to' => '2026-09-10T12:30:00+00:00',
            'limit' => 999,
            'offset' => 7,
        ]);

        self::assertSame('job-42', $criteria->jobKey);
        self::assertSame('billing', $criteria->component);
        self::assertSame('invoice', $criteria->resourceType);
        self::assertSame('completed', $criteria->status);
        self::assertSame('ops', $criteria->requestedBy);
        self::assertSame('2026-09-01T10:00:00+00:00', $criteria->createdFrom?->format(DATE_ATOM));
        self::assertSame('2026-09-10T12:30:00+00:00', $criteria->createdTo?->format(DATE_ATOM));
        self::assertSame(500, $criteria->limit);
        self::assertSame(7, $criteria->offset);

        $invalid = SearchReindexJobCriteria::fromArray([
            'jobKey' => [],
            'createdFrom' => 'not-a-date',
            'createdTo' => new \stdClass(),
            'limit' => 'nope',
            'offset' => -1,
        ], 25);

        self::assertNull($invalid->jobKey);
        self::assertNull($invalid->createdFrom);
        self::assertNull($invalid->createdTo);
        self::assertSame(25, $invalid->limit);
        self::assertSame(0, $invalid->offset);
    }

    public function testRelevanceCriteriaNormalizesQueryBooleanAndPagination(): void
    {
        $criteria = SearchRelevanceProfileCriteria::fromArray([
            'query' => ' premium ',
            'component' => ' catalog ',
            'resource_type' => ' product ',
            'enabled' => 'yes',
            'limit' => 300,
            'offset' => -3,
        ]);

        self::assertSame('premium', $criteria->nameEntity);
        self::assertSame('catalog', $criteria->component);
        self::assertSame('product', $criteria->resourceType);
        self::assertTrue($criteria->enabled);
        self::assertSame(200, $criteria->limit);
        self::assertSame(0, $criteria->offset);

        $invalid = SearchRelevanceProfileCriteria::fromArray(['enabled' => 'maybe', 'limit' => []], 41);
        self::assertNull($invalid->enabled);
        self::assertSame(41, $invalid->limit);
    }

    public function testSynonymCriteriaNormalizesAliasesBooleanAndPagination(): void
    {
        $criteria = SearchSynonymCriteria::fromArray([
            'locale' => ' en_US ',
            'query' => ' television ',
            'enabled' => false,
            'limit' => '250',
            'offset' => '5',
        ]);

        self::assertSame('en_US', $criteria->locale);
        self::assertSame('television', $criteria->sourceTerm);
        self::assertFalse($criteria->enabled);
        self::assertSame(200, $criteria->limit);
        self::assertSame(5, $criteria->offset);

        $invalid = SearchSynonymCriteria::fromArray([
            'locale' => [],
            'sourceTerm' => ' ',
            'enabled' => 'invalid',
            'offset' => new \stdClass(),
        ], 29);

        self::assertNull($invalid->locale);
        self::assertNull($invalid->sourceTerm);
        self::assertNull($invalid->enabled);
        self::assertSame(29, $invalid->limit);
        self::assertSame(0, $invalid->offset);
    }

    public function testIndexedResourceCriteriaNormalizesAliasesDatesStaleAndFallbacks(): void
    {
        $criteria = SearchIndexedResourceCriteria::fromArray([
            'component' => ' ordering ',
            'resource_type' => ' order ',
            'resource_id' => ' 42 ',
            'status' => ' indexed ',
            'stale' => 'true',
            'from' => '2026-09-01T00:00:00+00:00',
            'to' => '2026-09-15T00:00:00+00:00',
            'limit' => 999,
            'offset' => '5',
        ]);

        self::assertSame('ordering', $criteria->component);
        self::assertSame('order', $criteria->resourceType);
        self::assertSame('42', $criteria->resourceId);
        self::assertSame('indexed', $criteria->status);
        self::assertTrue($criteria->stale);
        self::assertSame('2026-09-01T00:00:00+00:00', $criteria->indexedFrom?->format(DATE_ATOM));
        self::assertSame('2026-09-15T00:00:00+00:00', $criteria->indexedTo?->format(DATE_ATOM));
        self::assertSame(500, $criteria->limit);
        self::assertSame(5, $criteria->offset);

        $fallback = SearchIndexedResourceCriteria::fromArray([
            'component' => [],
            'resource' => 123,
            'id' => ' ',
            'stale' => 'not-a-bool',
            'indexedFrom' => 'not-a-date',
            'indexedTo' => new \stdClass(),
            'limit' => 'bad',
            'offset' => -2,
        ], 27);
        self::assertNull($fallback->component);
        self::assertSame('123', $fallback->resourceType);
        self::assertNull($fallback->resourceId);
        self::assertNull($fallback->stale);
        self::assertNull($fallback->indexedFrom);
        self::assertNull($fallback->indexedTo);
        self::assertSame(27, $fallback->limit);
        self::assertSame(0, $fallback->offset);
    }

    public function testQueryLogCriteriaNormalizesAliasesAndInvalidInput(): void
    {
        $criteria = SearchQueryLogCriteria::fromArray([
            'limit' => '25',
            'offset' => '3',
            'query' => ' invoice ',
            'user_id' => ' user-1 ',
            'vendor_id' => ' vendor-1 ',
            'provider' => ' null ',
            'correlation_id' => ' corr-1 ',
            'request_id' => ' req-1 ',
            'source_component' => ' ordering ',
            'source_operation' => ' search ',
            'successful' => 'false',
            'from' => '2026-09-01T00:00:00+00:00',
            'to' => '2026-09-15T00:00:00+00:00',
        ]);

        self::assertSame(25, $criteria->limit);
        self::assertSame(3, $criteria->offset);
        self::assertSame('invoice', $criteria->query);
        self::assertSame('user-1', $criteria->userId);
        self::assertSame('vendor-1', $criteria->vendorId);
        self::assertSame('null', $criteria->providerName);
        self::assertSame('corr-1', $criteria->correlationId);
        self::assertSame('req-1', $criteria->requestId);
        self::assertSame('ordering', $criteria->sourceComponent);
        self::assertSame('search', $criteria->sourceOperation);
        self::assertFalse($criteria->successful);
        self::assertSame('2026-09-01T00:00:00+00:00', $criteria->from?->format(DATE_ATOM));
        self::assertSame('2026-09-15T00:00:00+00:00', $criteria->to?->format(DATE_ATOM));

        $fallback = SearchQueryLogCriteria::fromArray([
            'limit' => [],
            'offset' => 'bad',
            'query' => new \stdClass(),
            'successful' => 'maybe',
            'from' => new \stdClass(),
            'to' => 'invalid-date',
        ], 31);
        self::assertSame(31, $fallback->limit);
        self::assertSame(0, $fallback->offset);
        self::assertNull($fallback->query);
        self::assertNull($fallback->successful);
        self::assertNull($fallback->from);
        self::assertNull($fallback->to);
    }

    public function testQueryLogCriteriaRejectsInvalidConstructorBounds(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new SearchQueryLogCriteria(limit: 0);
    }

    public function testQueryLogCriteriaRejectsNegativeOffset(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new SearchQueryLogCriteria(offset: -1);
    }

    public function testCriteriaHelpersCoverEmptyNullAndNonIntegerFallbackBranches(): void
    {
        $index = SearchIndexCriteria::fromArray([
            'provider' => ' ',
            'component' => null,
            'resourceType' => 123,
            'enabled' => '',
            'limit' => 'not-an-int',
            'offset' => new \stdClass(),
        ], 17);
        self::assertNull($index->provider);
        self::assertNull($index->component);
        self::assertSame('123', $index->resourceType);
        self::assertNull($index->enabled);
        self::assertSame(17, $index->limit);
        self::assertSame(0, $index->offset);

        $jobs = SearchReindexJobCriteria::fromArray([
            'jobKey' => ' ',
            'component' => 321,
            'createdFrom' => '',
            'createdTo' => 'definitely-not-a-date',
            'limit' => new \stdClass(),
            'offset' => 'not-an-int',
        ], 19);
        self::assertNull($jobs->jobKey);
        self::assertSame('321', $jobs->component);
        self::assertNull($jobs->createdFrom);
        self::assertNull($jobs->createdTo);
        self::assertSame(19, $jobs->limit);
        self::assertSame(0, $jobs->offset);

        $relevance = SearchRelevanceProfileCriteria::fromArray([
            'nameEntity' => ' ',
            'component' => new \stdClass(),
            'enabled' => '',
            'limit' => 'not-an-int',
            'offset' => 2.5,
        ], 23);
        self::assertNull($relevance->nameEntity);
        self::assertNull($relevance->component);
        self::assertNull($relevance->enabled);
        self::assertSame(23, $relevance->limit);
        self::assertSame(0, $relevance->offset);

        $synonym = SearchSynonymCriteria::fromArray([
            'locale' => ' ',
            'source_term' => 456,
            'enabled' => '',
            'limit' => 'bad',
            'offset' => -5,
        ], 31);
        self::assertNull($synonym->locale);
        self::assertSame('456', $synonym->sourceTerm);
        self::assertNull($synonym->enabled);
        self::assertSame(31, $synonym->limit);
        self::assertSame(0, $synonym->offset);
    }
}
