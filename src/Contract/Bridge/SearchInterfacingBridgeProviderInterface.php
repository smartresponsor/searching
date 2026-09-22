<?php

declare(strict_types=1);

namespace App\Searching\Contract\Bridge;

use App\Searching\ValueObject\Bridge\SearchBridgeConfig;
use App\Searching\ValueObject\Query\SearchQueryRequest;
use App\Searching\ValueObject\Query\SearchSuggestionRequest;
use App\Searching\ValueObject\Result\SearchResponse;
use App\Searching\ValueObject\Result\SearchSuggestionResponse;

interface SearchInterfacingBridgeProviderInterface
{
    public function getBridgeConfig(): SearchBridgeConfig;

    public function search(SearchQueryRequest $query): SearchResponse;

    /**
     * @return list<SearchSuggestionResponse>
     */
    public function suggest(SearchSuggestionRequest $query): array;
}
