<?php

declare(strict_types=1);

namespace App\Searching\Service\Query;

use App\Searching\Contract\Query\SearchQueryLoggerInterface;
use App\Searching\ValueObject\Query\SearchQueryExecutionTrace;

/**
 * Defines the search null query logger responsibility within the Searching component runtime and its typed boundaries.
 */
final class SearchNullQueryLogger implements SearchQueryLoggerInterface
{
    /**
     * Executes the log responsibility defined by the Searching component contract.
     */
    public function log(SearchQueryExecutionTrace $trace): void
    {
        // Intentionally no-op. Host applications may replace this service with a Doctrine-backed logger.
    }
}
