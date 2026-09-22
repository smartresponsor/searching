<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service\Provider;

use App\Searching\Builder\Provider\SearchBackendQueryBuilder;
use App\Searching\Contract\Tuning\SearchQueryTuningResolverInterface;
use App\Searching\ValueObject\Provider\SearchProviderConfiguration;
use App\Searching\ValueObject\Query\SearchQuery;
use App\Searching\ValueObject\Tuning\SearchQueryTuning;
use PHPUnit\Framework\TestCase;

final class SearchBackendQueryBuilderTuningTest extends TestCase
{
    public function testItAppliesSynonymExpansionAndRelevanceWeights(): void
    {
        $resolver = new class implements SearchQueryTuningResolverInterface {
            public function resolve(SearchQuery $query): SearchQueryTuning
            {
                return new SearchQueryTuning(
                    expandedTerms: ['cell phone', 'smartphone'],
                    fieldWeights: ['title' => 8, 'summary' => 3, 'body' => 1],
                    matchedSynonyms: ['phone' => ['cell phone', 'smartphone']],
                    relevanceProfiles: ['catalog-product-default'],
                );
            }
        };

        $builder = new SearchBackendQueryBuilder($resolver);
        $backendQuery = $builder->build(
            new SearchQuery(query: 'phone', components: ['cataloging'], resourceTypes: ['product']),
            new SearchProviderConfiguration(
                nameEntity: 'opensearch',
                enabled: true,
                dsn: null,
                indexPrefix: 'sr',
                options: ['synonym_boost' => 0.5],
            ),
        );

        $payload = $backendQuery->toPayload();
        $queryPayload = $payload['query'];
        /** @var array{bool: array{must: list<array<string, mixed>>}} $queryPayload */
        $must = $queryPayload['bool']['must'];
        /** @var array{fields: list<string>} $primaryMatch */
        $primaryMatch = $must[0]['multi_match'];
        /** @var array{should: list<array{multi_match: array{query: string, boost: float}}>} $expanded */
        $expanded = $must[1]['bool'];
        $metadata = $payload['_searching'];
        /** @var array{tuning: array{expanded_terms: list<string>, relevance_profiles: list<string>}} $metadata */
        self::assertSame(['title^8', 'summary^3', 'body^1'], $primaryMatch['fields']);
        self::assertSame('cell phone', $expanded['should'][0]['multi_match']['query']);
        self::assertSame(0.5, $expanded['should'][0]['multi_match']['boost']);
        self::assertSame(['cell phone', 'smartphone'], $metadata['tuning']['expanded_terms']);
        self::assertSame(['catalog-product-default'], $metadata['tuning']['relevance_profiles']);
    }
}
