<?php

declare(strict_types=1);

namespace App\Searching\Tests\Value;

use App\Searching\Value\Indexing\SearchIndexCriteria;
use App\Searching\Value\Indexing\SearchReindexJobCriteria;
use App\Searching\Value\Provider\SearchProviderConfiguration;
use App\Searching\Value\Tuning\SearchRelevanceProfileCriteria;
use App\Searching\Value\Tuning\SearchSynonymCriteria;
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
}
