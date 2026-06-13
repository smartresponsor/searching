<?php

declare(strict_types=1);

namespace App\Searching\Value\Surface;

final readonly class SearchSurfaceFacet
{
    /**
     * @param array<string, int> $buckets
     */
    public function __construct(
        public string $nameEntity,
        public array $buckets,
    ) {
    }
}
