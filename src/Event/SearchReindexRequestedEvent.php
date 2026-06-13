<?php

declare(strict_types=1);

namespace App\Searching\Event;

final readonly class SearchReindexRequestedEvent
{
    public function __construct(public string $id)
    {
    }
}
