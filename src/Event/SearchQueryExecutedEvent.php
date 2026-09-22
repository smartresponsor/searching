<?php

declare(strict_types=1);

namespace App\Searching\Event;

/**
 * Defines the search query executed event responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchQueryExecutedEvent
{
    public function __construct(public string $id)
    {
    }
}
