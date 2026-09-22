<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Query;

/**
 * Defines the search sort responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchSort
{
    public function __construct(
        public string $field,
        public string $direction = 'asc',
    ) {
    }
}
