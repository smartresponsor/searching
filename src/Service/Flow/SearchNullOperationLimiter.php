<?php

declare(strict_types=1);

namespace App\Searching\Service\Flow;

use App\Searching\Contract\Flow\SearchOperationLimiterInterface;
use App\Searching\ValueObject\Flow\SearchOperationLimitDecision;
use App\Searching\ValueObject\Flow\SearchOperationLimitRequest;

/**
 * Defines the search null operation limiter responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchNullOperationLimiter implements SearchOperationLimiterInterface
{
    /**
     * Executes the decide responsibility defined by the Searching component contract.
     */
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
