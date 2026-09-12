<?php

declare(strict_types=1);

namespace App\Searching\Contract\Provider;

use App\Searching\Value\Document\SearchDocument;
use App\Searching\Value\Provider\SearchProviderResult;
use App\Searching\Value\Provider\SearchProviderStatus;
use App\Searching\Value\Query\SearchQuery;
use App\Searching\Value\Query\SearchSuggestionQuery;
use App\Searching\Value\Result\SearchSuggestion;

interface SearchProviderInterface
{
    public function index(SearchDocument $document): void;

    /**
     * @param iterable<SearchDocument> $documents
     */
    public function bulkIndex(iterable $documents): void;

    public function delete(string $component, string $resourceType, string $resourceId): void;

    public function search(SearchQuery $query): SearchProviderResult;

    /**
     * @return list<SearchSuggestion>
     */
    public function suggest(SearchSuggestionQuery $query): array;

    public function getStatus(): SearchProviderStatus;
}
