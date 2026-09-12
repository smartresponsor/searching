<?php

declare(strict_types=1);

namespace App\Searching\Service\Query;

use App\Searching\Contract\Query\SearchQueryLoggerInterface;
use App\Searching\Value\Query\SearchQueryExecutionTrace;

final class SearchNullQueryLogger implements SearchQueryLoggerInterface
{
    public function log(SearchQueryExecutionTrace $trace): void
    {
        // Intentionally no-op. Host applications may replace this service with a Doctrine-backed logger.
    }
}
