<?php

declare(strict_types=1);

namespace App\Searching\Value\Result;

final readonly class SearchFacetResponse
{
    public string $identifier;

    public string $nameEntity;

    /** @var array<string, int> */
    public array $buckets;

    /**
     * @param array<string, int> $buckets
     */
    public function __construct(
        string $nameEntity,
        array $buckets,
    ) {
        $facet = new SearchFacet($nameEntity, $buckets);
        $this->identifier = $facet->identifier;
        $this->nameEntity = $facet->nameEntity;
        $this->buckets = $facet->buckets;
    }
}
