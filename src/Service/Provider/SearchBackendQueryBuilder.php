<?php

declare(strict_types=1);

namespace App\Searching\Service\Provider;

use App\Searching\ServiceInterface\Provider\SearchBackendQueryBuilderInterface;
use App\Searching\ServiceInterface\Tuning\SearchQueryTuningResolverInterface;
use App\Searching\Value\Provider\SearchBackendQuery;
use App\Searching\Value\Provider\SearchProviderConfiguration;
use App\Searching\Value\Query\SearchQuery;

final class SearchBackendQueryBuilder implements SearchBackendQueryBuilderInterface
{
    public function __construct(private ?SearchQueryTuningResolverInterface $tuningResolver = null)
    {
    }

    public function build(SearchQuery $query, SearchProviderConfiguration $configuration): SearchBackendQuery
    {
        $must = [];
        $filter = [];
        $tuning = $this->tuningResolver?->resolve($query);

        $queryText = trim($query->query);
        if ('' === $queryText) {
            $must[] = ['match_all' => (object) []];
        } else {
            $must[] = [
                'multi_match' => [
                    'query' => $queryText,
                    'fields' => $this->searchFields($configuration, $tuning?->fieldWeights ?? []),
                    'type' => 'best_fields',
                    'operator' => $this->optionString($configuration, 'default_operator', 'and'),
                ],
            ];

            if (null !== $tuning && $tuning->hasExpandedTerms()) {
                $must[] = [
                    'bool' => [
                        'should' => array_map(
                            fn (string $term): array => [
                                'multi_match' => [
                                    'query' => $term,
                                    'fields' => $this->searchFields($configuration, $tuning->fieldWeights),
                                    'type' => 'best_fields',
                                    'operator' => 'or',
                                    'boost' => $this->optionFloat($configuration, 'synonym_boost', 0.75),
                                ],
                            ],
                            $tuning->expandedTerms,
                        ),
                        'minimum_should_match' => 0,
                    ],
                ];
            }
        }

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

        $backendQuery = ['bool' => ['must' => $must]];
        if ([] !== $filter) {
            $backendQuery['bool']['filter'] = $filter;
        }

        return new SearchBackendQuery(
            query: $backendQuery,
            filters: $query->filters,
            sort: $query->sort,
            from: max(0, ($query->page - 1) * $query->limit),
            size: $query->limit,
            highlight: $query->includeHighlights ? $this->highlightFields($configuration) : [],
            aggregations: $query->includeFacets ? $this->facetAggregations($configuration) : [],
            metadata: [
                'components' => $query->components,
                'resource_types' => $query->resourceTypes,
                'user_id' => $query->userId,
                'tuning' => $tuning?->toMetadata() ?? [],
                'execution_context' => $query->executionContext?->toMetadata() ?? [],
            ],
        );
    }

    /**
     * @param array<string, int|float> $fieldWeights
     *
     * @return list<string>
     */
    private function searchFields(SearchProviderConfiguration $configuration, array $fieldWeights = []): array
    {
        if ([] !== $fieldWeights) {
            $weighted = [];
            foreach ($fieldWeights as $field => $weight) {
                $fieldName = trim((string) $field);
                if ('' === $fieldName) {
                    continue;
                }

                $weightValue = (float) $weight;
                $weighted[] = $weightValue > 0 ? $fieldName.'^'.$this->formatWeight($weightValue) : $fieldName;
            }

            if ([] !== $weighted) {
                return $weighted;
            }
        }

        $fields = $configuration->options['search_fields'] ?? null;
        if (is_array($fields) && [] !== $fields) {
            return array_values(array_filter($fields, 'is_string'));
        }

        return ['title^4', 'summary^2', 'body', 'keywords^3'];
    }

    /**
     * @return array<string, mixed>
     */
    private function highlightFields(SearchProviderConfiguration $configuration): array
    {
        $fields = $configuration->options['highlight_fields'] ?? ['title', 'summary', 'body'];
        $fieldMap = [];

        foreach (is_array($fields) ? $fields : [] as $field) {
            if (is_string($field) && '' !== $field) {
                $fieldMap[$field] = new \stdClass();
            }
        }

        return [] === $fieldMap ? [] : ['fields' => $fieldMap];
    }

    /**
     * @return array<string, mixed>
     */
    private function facetAggregations(SearchProviderConfiguration $configuration): array
    {
        $facets = $configuration->options['facet_fields'] ?? [];
        $aggregations = [];

        foreach (is_array($facets) ? $facets : [] as $facet) {
            if (is_string($facet) && '' !== $facet) {
                $aggregations[$facet] = ['terms' => ['field' => $facet]];
            }
        }

        return $aggregations;
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

    private function optionString(SearchProviderConfiguration $configuration, string $nameEntity, string $default): string
    {
        $value = $configuration->options[$nameEntity] ?? $default;

        return is_string($value) && '' !== $value ? $value : $default;
    }

    private function optionFloat(SearchProviderConfiguration $configuration, string $nameEntity, float $default): float
    {
        $value = $configuration->options[$nameEntity] ?? $default;

        return is_numeric($value) ? (float) $value : $default;
    }

    private function formatWeight(float $weight): string
    {
        return rtrim(rtrim(sprintf('%.4F', $weight), '0'), '.');
    }
}
