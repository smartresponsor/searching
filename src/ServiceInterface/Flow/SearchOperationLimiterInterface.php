<?php

declare(strict_types=1);

namespace App\Searching\ServiceInterface\Flow;

use App\Searching\Value\Flow\SearchOperationLimitDecision;
use App\Searching\Value\Flow\SearchOperationLimitRequest;

interface SearchOperationLimiterInterface
{
    public function decide(SearchOperationLimitRequest $request): SearchOperationLimitDecision;
}
