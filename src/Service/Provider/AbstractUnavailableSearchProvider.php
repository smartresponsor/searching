<?php

declare(strict_types=1);

namespace App\Searching\Service\Provider;

use App\Searching\ServiceInterface\Provider\SearchProviderInterface;
use App\Searching\Value\Document\SearchDocument;
use App\Searching\Value\Provider\SearchProviderResult;
use App\Searching\Value\Provider\SearchProviderStatus;
use App\Searching\Value\Query\SearchQuery;
use App\Searching\Value\Query\SearchSuggestionQuery;

abstract class AbstractUnavailableSearchProvider implements SearchProviderInterface
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
