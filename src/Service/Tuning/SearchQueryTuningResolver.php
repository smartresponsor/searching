<?php

declare(strict_types=1);

namespace App\Searching\Service\Tuning;

use App\Searching\Entity\SearchRelevanceProfileEntity;
use App\Searching\ServiceInterface\Tuning\SearchQueryTuningResolverInterface;
use App\Searching\ServiceInterface\Tuning\SearchRelevanceProfileReaderInterface;
use App\Searching\ServiceInterface\Tuning\SearchSynonymReaderInterface;
use App\Searching\Value\Query\SearchQuery;
use App\Searching\Value\Tuning\SearchQueryTuning;
use App\Searching\Value\Tuning\SearchRelevanceProfileCriteria;
use App\Searching\Value\Tuning\SearchSynonymCriteria;

final readonly class SearchQueryTuningResolver implements SearchQueryTuningResolverInterface
{
    public function __construct(
        private SearchSynonymReaderInterface $synonymReader,
        private SearchRelevanceProfileReaderInterface $relevanceProfileReader,
    ) {
    }

    public function resolve(SearchQuery $query): SearchQueryTuning
    {
        $matchedSynonyms = $this->resolveSynonyms($query);
        $expandedTerms = $this->flattenSynonymTargets($matchedSynonyms);
        [$fieldWeights, $profileNames] = $this->resolveFieldWeights($query);

        return new SearchQueryTuning(
            expandedTerms: $expandedTerms,
            fieldWeights: $fieldWeights,
            matchedSynonyms: $matchedSynonyms,
            relevanceProfiles: $profileNames,
        );
    }

    /**
     * @return array<string, list<string>>
     */
    private function resolveSynonyms(SearchQuery $query): array
    {
        $queryText = $this->normalize($query->query);
        if ('' === $queryText) {
            return [];
        }

        $synonyms = $this->synonymReader->find(new SearchSynonymCriteria(
            locale: $query->locale,
            enabled: true,
            limit: 250,
        ));

        if (null !== $query->locale) {
            $synonyms = array_merge($synonyms, $this->synonymReader->find(new SearchSynonymCriteria(
                locale: null,
                enabled: true,
                limit: 250,
            )));
        }

        $matched = [];
        foreach ($synonyms as $synonym) {
            $source = $this->normalize($synonym->getSourceTerm());
            if ('' === $source || !$this->queryContainsTerm($queryText, $source)) {
                continue;
            }

            $targets = $this->normalizeTerms($synonym->getTargetTerms());
            if ([] !== $targets) {
                $matched[$synonym->getSourceTerm()] = $targets;
            }
        }

        return $matched;
    }

    /**
     * @return array{0: array<string, int|float>, 1: list<string>}
     */
    private function resolveFieldWeights(SearchQuery $query): array
    {
        $profiles = $this->relevanceProfileReader->find(new SearchRelevanceProfileCriteria(
            enabled: true,
            limit: 250,
        ));

        $weights = [];
        $profileNames = [];

        foreach ($profiles as $profile) {
            if (!$this->profileApplies($profile, $query)) {
                continue;
            }

            $profileNames[] = $profile->getName();
            foreach ($profile->getFieldWeights() as $field => $weight) {
                $fieldName = trim((string) $field);
                if ('' === $fieldName) {
                    continue;
                }

                $weights[$fieldName] = max((float) ($weights[$fieldName] ?? 0), (float) $weight);
            }
        }

        return [$weights, array_values(array_unique($profileNames))];
    }

    private function profileApplies(SearchRelevanceProfileEntity $profile, SearchQuery $query): bool
    {
        $component = $profile->getComponent();
        if (null !== $component && [] !== $query->components && !in_array($component, $query->components, true)) {
            return false;
        }

        if (null !== $component && [] === $query->components) {
            return false;
        }

        $resourceType = $profile->getResourceType();
        if (null !== $resourceType && [] !== $query->resourceTypes && !in_array($resourceType, $query->resourceTypes, true)) {
            return false;
        }

        if (null !== $resourceType && [] === $query->resourceTypes) {
            return false;
        }

        return true;
    }

    /**
     * @param array<string, list<string>> $matchedSynonyms
     *
     * @return list<string>
     */
    private function flattenSynonymTargets(array $matchedSynonyms): array
    {
        $terms = [];
        foreach ($matchedSynonyms as $targets) {
            foreach ($targets as $target) {
                $terms[] = $target;
            }
        }

        return array_values(array_unique($terms));
    }

    /**
     * @param list<string> $terms
     *
     * @return list<string>
     */
    private function normalizeTerms(array $terms): array
    {
        $normalized = [];
        foreach ($terms as $term) {
            $value = trim($term);
            if ('' !== $value) {
                $normalized[] = $value;
            }
        }

        return array_values(array_unique($normalized));
    }

    private function queryContainsTerm(string $queryText, string $term): bool
    {
        return 1 === preg_match('/(?:^|[^\pL\pN])'.preg_quote($term, '/').'(?:$|[^\pL\pN])/iu', $queryText);
    }

    private function normalize(string $value): string
    {
        return mb_strtolower(trim($value));
    }
}
