<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service\Provider;

use App\Searching\Service\Provider\SearchBackendQueryBuilder;
use App\Searching\ServiceInterface\Tuning\SearchQueryTuningResolverInterface;
use App\Searching\Value\Provider\SearchProviderConfiguration;
use App\Searching\Value\Query\SearchQuery;
use App\Searching\Value\Tuning\SearchQueryTuning;
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
                name: 'opensearch',
                enabled: true,
                dsn: null,
                indexPrefix: 'sr',
                options: ['synonym_boost' => 0.5],
            ),
        );

        $payload = $backendQuery->toPayload();
        $must = $payload['query']['bool']['must'];

        self::assertSame(['title^8', 'summary^3', 'body^1'], $must[0]['multi_match']['fields']);
        self::assertSame('cell phone', $must[1]['bool']['should'][0]['multi_match']['query']);
        self::assertSame(0.5, $must[1]['bool']['should'][0]['multi_match']['boost']);
        self::assertSame(['cell phone', 'smartphone'], $payload['_searching']['tuning']['expanded_terms']);
        self::assertSame(['catalog-product-default'], $payload['_searching']['tuning']['relevance_profiles']);
    }
}
