<?php

declare(strict_types=1);

namespace App\Searching\Provider\Backend;

use App\Searching\Contract\Provider\SearchProviderInterface;
use App\Searching\ValueObject\Document\SearchDocument;
use App\Searching\ValueObject\Provider\SearchProviderResult;
use App\Searching\ValueObject\Provider\SearchProviderStatus;
use App\Searching\ValueObject\Query\SearchQuery;
use App\Searching\ValueObject\Query\SearchSuggestionQuery;

abstract class SearchAbstractUnavailableProvider implements SearchProviderInterface
{
    public function index(SearchDocument $document): void
    {
        throw new \RuntimeException($this->getProviderName().' provider is not implemented yet.');
    }

    public function bulkIndex(iterable $documents): void
    {
        throw new \RuntimeException($this->getProviderName().' provider is not implemented yet.');
    }

    public function delete(string $component, string $resourceType, string $resourceId): void
    {
        throw new \RuntimeException($this->getProviderName().' provider is not implemented yet.');
    }

    public function search(SearchQuery $query): SearchProviderResult
    {
        throw new \RuntimeException($this->getProviderName().' provider is not implemented yet.');
    }

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
