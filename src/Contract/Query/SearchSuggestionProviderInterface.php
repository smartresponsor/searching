<?php

declare(strict_types=1);

namespace App\Searching\Contract\Query;

use App\Searching\ValueObject\Query\SearchSuggestionQuery;
use App\Searching\ValueObject\Result\SearchSuggestion;

/**
 * Defines the search suggestion provider interface responsibility within the Searching component runtime and its typed boundaries.
 */
interface SearchSuggestionProviderInterface
{
    /**
     * @return list<SearchSuggestion>
     */
    public function suggest(string $query, int $limit = 10): array;

    /**
     * @return list<SearchSuggestion>
     */
    public function suggestByQuery(SearchSuggestionQuery $query): array;
}
