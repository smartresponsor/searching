<?php

declare(strict_types=1);

namespace App\Searching\Value\Result;

final readonly class SearchFacetResponse
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
