<?php

declare(strict_types=1);

namespace App\Searching\Contract\Health;

use App\Searching\ValueObject\Health\SearchHealthReport;

/**
 * Defines the search health checker interface responsibility within the Searching component runtime and its typed boundaries.
 */
interface SearchHealthCheckerInterface
{
    /**
     * Executes the check responsibility defined by the Searching component contract.
     */
    public function check(): SearchHealthReport;
}
