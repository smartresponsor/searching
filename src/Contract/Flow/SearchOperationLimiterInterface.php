<?php

declare(strict_types=1);

namespace App\Searching\Contract\Flow;

use App\Searching\ValueObject\Flow\SearchOperationLimitDecision;
use App\Searching\ValueObject\Flow\SearchOperationLimitRequest;

/**
 * Defines the search operation limiter interface responsibility within the Searching component runtime and its typed boundaries.
 */
interface SearchOperationLimiterInterface
{
    /**
     * Executes the decide responsibility defined by the Searching component contract.
     */
    public function decide(SearchOperationLimitRequest $request): SearchOperationLimitDecision;
}
