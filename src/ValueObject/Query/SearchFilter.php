<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Query;

/**
 * Defines the search filter responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchFilter
{
    public function __construct(
        public string $field,
        public mixed $value,
        public string $operator = 'eq',
    ) {
    }
}
