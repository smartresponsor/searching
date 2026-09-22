<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Result;

/**
 * Defines the search highlight response responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchHighlightResponse
{
    /**
     * @param list<string> $fragments
     */
    public function __construct(
        public string $field,
        public array $fragments,
    ) {
    }
}
