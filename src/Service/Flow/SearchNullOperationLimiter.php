<?php

declare(strict_types=1);

namespace App\Searching\Service\Flow;

use App\Searching\Contract\Flow\SearchOperationLimiterInterface;
use App\Searching\Value\Flow\SearchOperationLimitDecision;
use App\Searching\Value\Flow\SearchOperationLimitRequest;

final readonly class SearchNullOperationLimiter implements SearchOperationLimiterInterface
{
    public function decide(SearchOperationLimitRequest $request): SearchOperationLimitDecision
    {
        return SearchOperationLimitDecision::allow([
            'limiter' => 'null',
            'operation' => $request->operation,
            'identity' => $request->identity,
            'cost' => $request->cost,
        ]);
    }
}
