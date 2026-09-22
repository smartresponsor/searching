<?php

declare(strict_types=1);

namespace App\Searching\Contract\Query;

use App\Searching\ValueObject\Query\SearchQueryExecutionTrace;

interface SearchQueryLoggerInterface
{
    public function log(SearchQueryExecutionTrace $trace): void;
}
