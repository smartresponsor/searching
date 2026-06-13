<?php

declare(strict_types=1);

namespace App\Searching\Service\Query;

use App\Searching\ServiceInterface\Provider\SearchProviderInterface;
use App\Searching\ServiceInterface\Query\SearchSuggestionProviderInterface;
use App\Searching\Value\Query\SearchSuggestionQuery;

final readonly class SearchSuggestionProvider implements SearchSuggestionProviderInterface
{
    public function __construct(private SearchProviderInterface $searchProvider)
    {
    }

    public function suggest(string $query, int $limit = 10): array
    {
        return $this->suggestByQuery(new SearchSuggestionQuery(query: $query, limit: $limit));
    }

    public function suggestByQuery(SearchSuggestionQuery $query): array
    {
        if ('' === trim($query->query)) {
            return [];
        }

        return $this->searchProvider->suggest($query);
    }
}
