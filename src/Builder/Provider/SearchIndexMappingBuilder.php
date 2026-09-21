<?php

declare(strict_types=1);

namespace App\Searching\Builder\Provider;

use App\Searching\Contract\Provider\SearchIndexMappingBuilderInterface;
use App\Searching\Value\Provider\SearchIndexMapping;
use App\Searching\Value\Provider\SearchProviderConfiguration;

final class SearchIndexMappingBuilder implements SearchIndexMappingBuilderInterface
{
    public function build(string $indexName, string $component, string $resourceType, SearchProviderConfiguration $configuration): SearchIndexMapping
    {
        $textAnalyzer = $this->optionString($configuration, 'text_analyzer', 'standard');
        $keywordNormalizer = $this->optionString($configuration, 'keyword_normalizer', 'lowercase');

        return new SearchIndexMapping(
            indexName: $indexName,
            settings: [
                'analysis' => [
                    'normalizer' => [
                        $keywordNormalizer => [
                            'type' => 'custom',
                            'filter' => ['lowercase'],
                        ],
                    ],
                ],
            ],
            properties: [
                'component' => ['type' => 'keyword', 'normalizer' => $keywordNormalizer],
                'resource_type' => ['type' => 'keyword', 'normalizer' => $keywordNormalizer],
                'resource_id' => ['type' => 'keyword'],
                'title' => ['type' => 'text', 'analyzer' => $textAnalyzer, 'fields' => ['keyword' => ['type' => 'keyword']]],
                'summary' => ['type' => 'text', 'analyzer' => $textAnalyzer],
                'body' => ['type' => 'text', 'analyzer' => $textAnalyzer],
                'keywords' => ['type' => 'keyword', 'normalizer' => $keywordNormalizer],
                'facets' => ['type' => 'object', 'enabled' => true],
                'permissions' => ['type' => 'keyword'],
                'locale' => ['type' => 'keyword', 'normalizer' => $keywordNormalizer],
                'vendor_id' => ['type' => 'keyword'],
                'owner_id' => ['type' => 'keyword'],
                'route_name' => ['type' => 'keyword'],
                'route_parameters' => ['type' => 'object', 'enabled' => true],
                'updated_at' => ['type' => 'date'],
            ],
            metadata: [
                'component' => $component,
                'resource_type' => $resourceType,
                'managed_by' => 'searching',
                'provider' => $configuration->nameEntity,
            ],
        );
    }

    private function optionString(SearchProviderConfiguration $configuration, string $nameEntity, string $default): string
    {
        $value = $configuration->options[$nameEntity] ?? $default;

        return is_string($value) && '' !== $value ? $value : $default;
    }
}
