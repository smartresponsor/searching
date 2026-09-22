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
 * Defines the search abstract unavailable provider responsibility within the Searching component runtime and its typed boundaries.
 */
abstract class SearchAbstractUnavailableProvider implements SearchProviderInterface
{
    /**
     * Indexes the index through the Searching component indexing boundary.
     */
    public function index(SearchDocument $document): void
    {
        throw new \RuntimeException($this->getProviderName().' provider is not implemented yet.');
    }

    /**
     * Executes the bulk index responsibility defined by the Searching component contract.
     */
    public function bulkIndex(iterable $documents): void
    {
        throw new \RuntimeException($this->getProviderName().' provider is not implemented yet.');
    }

    /**
     * Deletes the delete through the Searching component mutation boundary.
     */
    public function delete(string $component, string $resourceType, string $resourceId): void
    {
        throw new \RuntimeException($this->getProviderName().' provider is not implemented yet.');
    }

    /**
     * Searches the search through the Searching component query boundary.
     */
    public function search(SearchQuery $query): SearchProviderResult
    {
        throw new \RuntimeException($this->getProviderName().' provider is not implemented yet.');
    }

    /**
     * Builds suggestions for the suggest through the Searching component query boundary.
     */
    public function suggest(SearchSuggestionQuery $query): array
    {
        throw new \RuntimeException($this->getProviderName().' provider is not implemented yet.');
    }

    public function getStatus(): SearchProviderStatus
    {
        return new SearchProviderStatus($this->getProviderName(), false, 'not_implemented');
    }

    abstract protected function getProviderName(): string;
}
