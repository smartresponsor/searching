<?php

declare(strict_types=1);

namespace App\Searching\Contract\Query;

use App\Searching\ValueObject\Query\SearchSuggestionRequest;
use App\Searching\ValueObject\Result\SearchSuggestionResponse;

/**
 * Defines the search suggestion response provider interface responsibility within the Searching component runtime and its typed boundaries.
 */
interface SearchSuggestionResponseProviderInterface
{
    /**
     * @return list<SearchSuggestionResponse>
     */
    public function suggest(SearchSuggestionRequest $query): array;
}
