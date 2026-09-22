<?php

declare(strict_types=1);

namespace App\Searching\Exception;

use App\Searching\ValueObject\Flow\SearchOperationLimitDecision;
use App\Searching\ValueObject\Flow\SearchOperationLimitRequest;

/**
 * Defines the search operation limited exception responsibility within the Searching component runtime and its typed boundaries.
 */
final class SearchOperationLimitedException extends \RuntimeException
{
    public function __construct(
        public readonly SearchOperationLimitRequest $request,
        public readonly SearchOperationLimitDecision $decision,
    ) {
        parent::__construct($decision->reason ?? 'Search operation was limited.');
    }
}
