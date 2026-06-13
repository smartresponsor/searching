<?php

declare(strict_types=1);

namespace App\Searching\ServiceInterface\Bridge;

use App\Searching\Value\Bridge\SearchBridgeSurfaceConfig;
use App\Searching\Value\Surface\SearchSurfaceQuery;
use App\Searching\Value\Surface\SearchSurfaceResult;
use App\Searching\Value\Surface\SearchSurfaceSuggestion;
use App\Searching\Value\Surface\SearchSurfaceSuggestionQuery;

interface InterfacingSearchBridgeProviderInterface
{
    public function getSurfaceConfig(): SearchBridgeSurfaceConfig;

    public function search(SearchSurfaceQuery $query): SearchSurfaceResult;

    /**
     * @return list<SearchSurfaceSuggestion>
     */
    public function suggest(SearchSurfaceSuggestionQuery $query): array;
}
