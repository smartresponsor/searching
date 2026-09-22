<?php

declare(strict_types=1);

namespace App\Searching\Contract\Provider;

use App\Searching\ValueObject\Document\SearchDocument;
use App\Searching\ValueObject\Provider\SearchProviderResult;
use App\Searching\ValueObject\Provider\SearchProviderStatus;
use App\Searching\ValueObject\Query\SearchQuery;
use App\Searching\ValueObject\Query\SearchSuggestionQuery;
use App\Searching\ValueObject\Result\SearchSuggestion;

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
