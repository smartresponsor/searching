<?php

declare(strict_types=1);

namespace App\Searching\Value\Query;

final readonly class SearchSort
{
    public function __construct(
        public string $field,
        public string $direction = 'asc',
    ) {
    }
}
