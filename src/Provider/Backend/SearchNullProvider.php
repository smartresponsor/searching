<?php

declare(strict_types=1);

namespace App\Searching\Provider\Backend;

use App\Searching\Contract\Provider\SearchProviderInterface;
use App\Searching\ValueObject\Document\SearchDocument;
use App\Searching\ValueObject\Provider\SearchProviderResult;
use App\Searching\ValueObject\Provider\SearchProviderStatus;
use App\Searching\ValueObject\Query\SearchQuery;
use App\Searching\ValueObject\Query\SearchSuggestionQuery;

/**
 * Defines the search null provider responsibility within the Searching component runtime and its typed boundaries.
 */
final class SearchNullProvider implements SearchProviderInterface
{
    /**
     * Indexes the index through the Searching component indexing boundary.
     */
    public function index(SearchDocument $document): void
    {
    }

    /**
     * Executes the bulk index responsibility defined by the Searching component contract.
     */
    public function bulkIndex(iterable $documents): void
    {
        foreach ($documents as $_document) {
        }
    }

    /**
     * Deletes the delete through the Searching component mutation boundary.
     */
    public function delete(string $component, string $resourceType, string $resourceId): void
    {
    }

    /**
     * Searches the search through the Searching component query boundary.
     */
    public function search(SearchQuery $query): SearchProviderResult
    {
        return new SearchProviderResult(total: 0, items: []);
    }

    /**
     * Builds suggestions for the suggest through the Searching component query boundary.
     */
    public function suggest(SearchSuggestionQuery $query): array
    {
        return [];
    }

    public function getStatus(): SearchProviderStatus
    {
        return new SearchProviderStatus('null', true, 'disabled');
    }
}
