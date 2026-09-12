<?php

declare(strict_types=1);

namespace App\Searching\Contract\Query;

use App\Searching\Value\Query\SearchSuggestionRequest;
use App\Searching\Value\Result\SearchSuggestionResponse;

interface SearchSuggestionResponseProviderInterface
{
    /**
     * @return list<SearchSuggestionResponse>
     */
    public function suggest(SearchSuggestionRequest $query): array;
}
