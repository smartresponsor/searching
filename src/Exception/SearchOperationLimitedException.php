<?php

declare(strict_types=1);

namespace App\Searching\Exception;

use App\Searching\ValueObject\Flow\SearchOperationLimitDecision;
use App\Searching\ValueObject\Flow\SearchOperationLimitRequest;

final class SearchOperationLimitedException extends \RuntimeException
{
    public function __construct(
        public readonly SearchOperationLimitRequest $request,
        public readonly SearchOperationLimitDecision $decision,
    ) {
        parent::__construct($decision->reason ?? 'Search operation was limited.');
    }
}
