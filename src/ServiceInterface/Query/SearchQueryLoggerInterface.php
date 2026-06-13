<?php

declare(strict_types=1);

namespace App\Searching\ServiceInterface\Query;

use App\Searching\Value\Query\SearchQueryExecutionTrace;

interface SearchQueryLoggerInterface
{
    public function log(SearchQueryExecutionTrace $trace): void;
}
