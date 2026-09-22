<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Result;

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
