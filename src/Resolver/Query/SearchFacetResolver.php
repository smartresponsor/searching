<?php

declare(strict_types=1);

namespace App\Searching\Resolver\Query;

final class SearchFacetResolver
{
    /**
     * @param array<string, mixed> $facets
     *
     * @return array<string, mixed>
     */
    public function resolve(array $facets): array
    {
        return $facets;
    }
}
