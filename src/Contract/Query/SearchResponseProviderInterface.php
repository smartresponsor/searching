<?php

declare(strict_types=1);

namespace App\Searching\Contract\Query;

use App\Searching\Value\Provider\SearchCapability;
use App\Searching\Value\Query\SearchQueryRequest;
use App\Searching\Value\Result\SearchResponse;

interface SearchResponseProviderInterface
{
    public function search(SearchQueryRequest $query): SearchResponse;

    public function getCapability(): SearchCapability;
}
