<?php

declare(strict_types=1);

namespace App\Searching\Provider\Query;

use App\Searching\Contract\Provider\SearchProviderInterface;
use App\Searching\Contract\Query\SearchSuggestionProviderInterface;
use App\Searching\ValueObject\Query\SearchSuggestionQuery;

/**
 * Defines the search suggestion provider responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchSuggestionProvider implements SearchSuggestionProviderInterface
{
    public function __construct(private SearchProviderInterface $searchProvider)
    {
    }

    /**
     * Builds suggestions for the suggest through the Searching component query boundary.
     */
    public function suggest(string $query, int $limit = 10): array
    {
        return $this->suggestByQuery(new SearchSuggestionQuery(query: $query, limit: $limit));
    }

    /**
     * Builds suggestions for the by query through the Searching component query boundary.
     */
    public function suggestByQuery(SearchSuggestionQuery $query): array
    {
        if ('' === trim($query->query)) {
            return [];
        }

        return $this->searchProvider->suggest($query);
    }
}
