<?php

declare(strict_types=1);

namespace App\Searching\Contract\Flow;

use App\Searching\ValueObject\Flow\SearchOperationLimitDecision;
use App\Searching\ValueObject\Flow\SearchOperationLimitRequest;

interface SearchOperationLimiterInterface
{
    public function decide(SearchOperationLimitRequest $request): SearchOperationLimitDecision;
}
