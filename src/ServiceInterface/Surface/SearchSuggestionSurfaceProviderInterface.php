<?php

declare(strict_types=1);

namespace App\Searching\ServiceInterface\Surface;

use App\Searching\Value\Surface\SearchSurfaceSuggestion;
use App\Searching\Value\Surface\SearchSurfaceSuggestionQuery;

interface SearchSuggestionSurfaceProviderInterface
{
    /**
     * @return list<SearchSurfaceSuggestion>
     */
    public function suggest(SearchSurfaceSuggestionQuery $query): array;
}
