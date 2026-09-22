<?php

declare(strict_types=1);

namespace App\Searching\Contract\Provider;

use App\Searching\ValueObject\Document\SearchDocument;
use App\Searching\ValueObject\Provider\SearchProviderResult;
use App\Searching\ValueObject\Provider\SearchProviderStatus;
use App\Searching\ValueObject\Query\SearchQuery;
use App\Searching\ValueObject\Query\SearchSuggestionQuery;
use App\Searching\ValueObject\Result\SearchSuggestion;

/**
 * Defines the search provider interface responsibility within the Searching component runtime and its typed boundaries.
 */
interface SearchProviderInterface
{
    /**
     * Indexes the index through the Searching component indexing boundary.
     */
    public function index(SearchDocument $document): void;

    /**
     * @param iterable<SearchDocument> $documents
     */
    public function bulkIndex(iterable $documents): void;

    /**
     * Deletes the delete through the Searching component mutation boundary.
     */
    public function delete(string $component, string $resourceType, string $resourceId): void;

    /**
     * Searches the search through the Searching component query boundary.
     */
    public function search(SearchQuery $query): SearchProviderResult;

    /**
     * @return list<SearchSuggestion>
     */
    public function suggest(SearchSuggestionQuery $query): array;

    public function getStatus(): SearchProviderStatus;
}
