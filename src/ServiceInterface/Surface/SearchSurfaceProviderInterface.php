<?php

declare(strict_types=1);

namespace App\Searching\ServiceInterface\Surface;

use App\Searching\Value\Surface\SearchSurfaceCapability;
use App\Searching\Value\Surface\SearchSurfaceQuery;
use App\Searching\Value\Surface\SearchSurfaceResult;

interface SearchSurfaceProviderInterface
{
    public function search(SearchSurfaceQuery $query): SearchSurfaceResult;

    public function getCapability(): SearchSurfaceCapability;
}
