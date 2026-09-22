<?php

declare(strict_types=1);

namespace App\Searching\Contract\Query;

use App\Searching\ValueObject\Query\SearchQueryExecutionTrace;

/**
 * Defines the search query logger interface responsibility within the Searching component runtime and its typed boundaries.
 */
interface SearchQueryLoggerInterface
{
    /**
     * Executes the log responsibility defined by the Searching component contract.
     */
    public function log(SearchQueryExecutionTrace $trace): void;
}
