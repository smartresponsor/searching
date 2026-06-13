<?php

declare(strict_types=1);

namespace App\Searching\Service\Provider;

use App\Searching\ServiceInterface\Provider\SearchBackendSuggestionBuilderInterface;
use App\Searching\ServiceInterface\Tuning\SearchQueryTuningResolverInterface;
use App\Searching\Value\Provider\SearchBackendQuery;
use App\Searching\Value\Provider\SearchProviderConfiguration;
use App\Searching\Value\Query\SearchSuggestionQuery;

final class SearchBackendSuggestionBuilder implements SearchBackendSuggestionBuilderInterface
{
    public function __construct(private ?SearchQueryTuningResolverInterface $tuningResolver = null)
    {
    }

    public function build(SearchSuggestionQuery $query, SearchProviderConfiguration $configuration): SearchBackendQuery
    {
        $searchQuery = $query->toSearchQuery();
        $tuning = $query->includeSynonyms ? $this->tuningResolver?->resolve($searchQuery) : null;
        $queryText = trim($query->query);
        $filter = [];

        $this->appendTermsFilter($filter, 'component', $query->components);
        $this->appendTermsFilter($filter, 'resource_type', $query->resourceTypes);

        if (null !== $query->locale) {
            $filter[] = ['term' => ['locale' => $query->locale]];
        }

        if (null !== $query->tenantId) {
            $filter[] = ['term' => ['tenant_id' => $query->tenantId]];
        }

        foreach ($query->filters as $field => $value) {
            $filter[] = is_array($value)
                ? ['terms' => [$field => array_values($value)]]
                : ['term' => [$field => $value]];
        }

        $should = [];
        if ('' !== $queryText) {
            $should[] = [
                'multi_match' => [
                    'query' => $queryText,
                    'fields' => $this->suggestionFields($configuration),
                    'type' => 'phrase_prefix',
                    'boost' => $this->optionFloat($configuration, 'suggestion_prefix_boost', 2.0),
                ],
            ];

            if ($query->includeFuzzy) {
                $should[] = [
                    'multi_match' => [
                        'query' => $queryText,
                        'fields' => $this->suggestionFields($configuration),
                        'type' => 'best_fields',
                        'fuzziness' => 'AUTO',
                        'boost' => $this->optionFloat($configuration, 'suggestion_fuzzy_boost', 0.5),
                    ],
                ];
            }

            foreach ($tuning?->expandedTerms ?? [] as $term) {
                $should[] = [
                    'multi_match' => [
                        'query' => $term,
                        'fields' => $this->suggestionFields($configuration),
                        'type' => 'phrase_prefix',
                        'boost' => $this->optionFloat($configuration, 'suggestion_synonym_boost', 0.75),
                    ],
                ];
            }
        }

        $backendQuery = [
            'bool' => [
                'should' => [] !== $should ? $should : [['match_all' => (object) []]],
                'minimum_should_match' => [] !== $should ? 1 : 0,
            ],
        ];

        if ([] !== $filter) {
            $backendQuery['bool']['filter'] = $filter;
        }

        return new SearchBackendQuery(
            query: $backendQuery,
            filters: $query->filters,
            sort: ['_score' => 'desc'],
            from: 0,
            size: max(1, min(50, $query->limit)),
            highlight: [],
            aggregations: [],
            metadata: [
                'suggestion' => true,
                'components' => $query->components,
                'resource_types' => $query->resourceTypes,
                'user_id' => $query->userId,
                'tuning' => $tuning?->toMetadata() ?? [],
                'execution_context' => $query->executionContext?->toMetadata() ?? [],
            ],
        );
    }

    /**
     * @return list<string>
     */
    private function suggestionFields(SearchProviderConfiguration $configuration): array
    {
        $fields = $configuration->options['suggestion_fields'] ?? null;
        if (is_array($fields) && [] !== $fields) {
            return array_values(array_filter($fields, 'is_string'));
        }

        return ['title^5', 'keywords^3', 'summary^2'];
    }

    /**
     * @param list<array<string, mixed>> $filter
     * @param list<string>               $values
     */
    private function appendTermsFilter(array &$filter, string $field, array $values): void
    {
        if ([] !== $values) {
            $filter[] = ['terms' => [$field => $values]];
        }
    }

    private function optionFloat(SearchProviderConfiguration $configuration, string $nameEntity, float $default): float
    {
        $value = $configuration->options[$nameEntity] ?? $default;

        return is_numeric($value) ? (float) $value : $default;
    }
}
