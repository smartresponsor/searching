<?php

declare(strict_types=1);

namespace App\Searching\Value\Surface;

final readonly class SearchSurfaceHighlight
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
