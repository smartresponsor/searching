<?php

declare(strict_types=1);

namespace App\Searching\Provider\Backend;

use App\Searching\Contract\Provider\SearchProviderInterface;
use App\Searching\ValueObject\Document\SearchDocument;
use App\Searching\ValueObject\Provider\SearchProviderResult;
use App\Searching\ValueObject\Provider\SearchProviderStatus;
use App\Searching\ValueObject\Query\SearchQuery;
use App\Searching\ValueObject\Query\SearchSuggestionQuery;

final class SearchNullProvider implements SearchProviderInterface
{
    public function index(SearchDocument $document): void
    {
    }

    public function bulkIndex(iterable $documents): void
    {
        foreach ($documents as $_document) {
        }
    }

    public function delete(string $component, string $resourceType, string $resourceId): void
    {
    }

    public function search(SearchQuery $query): SearchProviderResult
    {
        return new SearchProviderResult(total: 0, items: []);
    }

    public function suggest(SearchSuggestionQuery $query): array
    {
        return [];
    }

    public function getStatus(): SearchProviderStatus
    {
        return new SearchProviderStatus('null', true, 'disabled');
    }
}
