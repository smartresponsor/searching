<?php

declare(strict_types=1);

namespace App\Searching\ServiceInterface\Query;

use App\Searching\Value\Query\SearchSuggestionQuery;
use App\Searching\Value\Result\SearchSuggestion;

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
