<?php

declare(strict_types=1);

namespace App\Searching\Value\Query;

final readonly class SearchFilter
{
    public function __construct(
        public string $field,
        public mixed $value,
        public string $operator = 'eq',
    ) {
    }
}
