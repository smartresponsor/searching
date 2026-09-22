<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Document;

/**
 * Defines the search document field responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchDocumentField
{
    public function __construct(
        public string $nameEntity,
        public mixed $value,
        public bool $searchable = true,
        public bool $filterable = false,
        public bool $facetable = false,
        public int $weight = 1,
    ) {
    }
}
