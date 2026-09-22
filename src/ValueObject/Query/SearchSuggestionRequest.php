<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Query;

use App\Searching\ValueObject\Observability\SearchExecutionContext;

/**
 * Defines the search suggestion request responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchSuggestionRequest
{
    /**
     * @param list<string>         $components
     * @param list<string>         $resourceTypes
     * @param array<string, mixed> $filters
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public string $query,
        public array $components = [],
        public array $resourceTypes = [],
        public array $filters = [],
        public int $limit = 10,
        public ?string $locale = null,
        public ?string $vendorId = null,
        public ?string $userId = null,
        public bool $includeSynonyms = true,
        public bool $includeFuzzy = true,
        public ?SearchExecutionContext $executionContext = null,
        public array $metadata = [],
    ) {
    }

    /**
     * Executes the to internal query responsibility defined by the Searching component contract.
     */
    public function toInternalQuery(): SearchSuggestionQuery
    {
        return new SearchSuggestionQuery(
            query: $this->query,
            components: $this->components,
            resourceTypes: $this->resourceTypes,
            filters: $this->filters,
            limit: $this->limit,
            locale: $this->locale,
            vendorId: $this->vendorId,
            userId: $this->userId,
            includeSynonyms: $this->includeSynonyms,
            includeFuzzy: $this->includeFuzzy,
            executionContext: $this->executionContext,
        );
    }
}
