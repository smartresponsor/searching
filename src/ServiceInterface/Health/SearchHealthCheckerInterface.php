<?php

declare(strict_types=1);

namespace App\Searching\ServiceInterface\Health;

use App\Searching\Value\Health\SearchHealthReport;

interface SearchHealthCheckerInterface
{
    public function check(): SearchHealthReport;
}
