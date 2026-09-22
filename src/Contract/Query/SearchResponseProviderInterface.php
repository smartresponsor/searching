<?php

declare(strict_types=1);

namespace App\Searching\Contract\Query;

use App\Searching\ValueObject\Provider\SearchCapability;
use App\Searching\ValueObject\Query\SearchQueryRequest;
use App\Searching\ValueObject\Result\SearchResponse;

interface SearchResponseProviderInterface
{
    public function search(SearchQueryRequest $query): SearchResponse;

    public function getCapability(): SearchCapability;
}
