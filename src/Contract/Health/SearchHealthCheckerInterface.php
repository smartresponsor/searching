<?php

declare(strict_types=1);

namespace App\Searching\Contract\Health;

use App\Searching\ValueObject\Health\SearchHealthReport;

interface SearchHealthCheckerInterface
{
    public function check(): SearchHealthReport;
}
