<?php

declare(strict_types=1);

namespace App\Searching\Event;

final readonly class SearchQueryExecutedEvent
{
    public function __construct(public string $id)
    {
    }
}
