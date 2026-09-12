<?php

declare(strict_types=1);

namespace App\Searching\Contract\Bridge;

use App\Searching\Value\Bridge\SearchBridgeConfig;
use App\Searching\Value\Query\SearchQueryRequest;
use App\Searching\Value\Query\SearchSuggestionRequest;
use App\Searching\Value\Result\SearchResponse;
use App\Searching\Value\Result\SearchSuggestionResponse;

interface SearchInterfacingBridgeProviderInterface
{
    public function getBridgeConfig(): SearchBridgeConfig;

    public function search(SearchQueryRequest $query): SearchResponse;

    /**
     * @return list<SearchSuggestionResponse>
     */
    public function suggest(SearchSuggestionRequest $query): array;
}
