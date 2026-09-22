<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Tuning;

/**
 * Defines the search query tuning responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchQueryTuning
{
    /**
     * @param list<string>                $expandedTerms
     * @param array<string, int|float>    $fieldWeights
     * @param array<string, list<string>> $matchedSynonyms
     * @param list<string>                $relevanceProfiles
     */
    public function __construct(
        public array $expandedTerms = [],
        public array $fieldWeights = [],
        public array $matchedSynonyms = [],
        public array $relevanceProfiles = [],
    ) {
    }

    public function hasExpandedTerms(): bool
    {
        return [] !== $this->expandedTerms;
    }

    public function hasFieldWeights(): bool
    {
        return [] !== $this->fieldWeights;
    }

    /**
     * @return array<string, mixed>
     */
    public function toMetadata(): array
    {
        return [
            'expanded_terms' => $this->expandedTerms,
            'field_weights' => $this->fieldWeights,
            'matched_synonyms' => $this->matchedSynonyms,
            'relevance_profiles' => $this->relevanceProfiles,
        ];
    }
}
