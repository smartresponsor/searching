<?php

declare(strict_types=1);

namespace App\Searching\Provider;

use App\Searching\Contract\Provider\SearchProviderInterface;
use App\Searching\Contract\Query\SearchQueryExecutorInterface;
use App\Searching\Contract\Query\SearchResponseProviderInterface;
use App\Searching\Contract\Query\SearchSuggestionProviderInterface;
use App\Searching\Contract\Query\SearchSuggestionResponseProviderInterface;
use App\Searching\Contract\Registry\SearchableResourceRegistryInterface;
use App\Searching\Service\SearchResponseMapper;
use App\Searching\ValueObject\Provider\SearchCapability;
use App\Searching\ValueObject\Query\SearchQueryRequest;
use App\Searching\ValueObject\Query\SearchSuggestionRequest;
use App\Searching\ValueObject\Result\SearchResponse;

/**
 * Defines the search response provider responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchResponseProvider implements SearchResponseProviderInterface, SearchSuggestionResponseProviderInterface
{
    public function __construct(
        private SearchQueryExecutorInterface $queryExecutor,
        private SearchSuggestionProviderInterface $suggestionProvider,
        private SearchProviderInterface $searchProvider,
        private SearchableResourceRegistryInterface $resourceRegistry,
        private SearchResponseMapper $responseMapper,
    ) {
    }

    /**
     * Searches the search through the Searching component query boundary.
     */
    public function search(SearchQueryRequest $query): SearchResponse
    {
        return $this->responseMapper->mapResult($this->queryExecutor->execute($query->toInternalQuery()));
    }

    /**
     * Builds suggestions for the suggest through the Searching component query boundary.
     */
    public function suggest(SearchSuggestionRequest $query): array
    {
        return array_map(
            $this->responseMapper->mapSuggestion(...),
            $this->suggestionProvider->suggestByQuery($query->toInternalQuery()),
        );
    }

    public function getCapability(): SearchCapability
    {
        $status = $this->searchProvider->getStatus();
        $resources = $this->resourceRegistry->definitions();

        $components = [];
        $resourceTypes = [];

        foreach ($resources as $resource) {
            $components[] = $resource->component;
            $resourceTypes[] = $resource->resourceType;
        }

        return new SearchCapability(
            enabled: $status->available,
            providerName: $status->nameEntity,
            supportedFeatures: [
                'query',
                'suggestions',
                'facets',
                'highlights',
                'route_targets',
                'permission_filtered_results',
            ],
            supportedComponents: array_values(array_unique($components)),
            supportedResourceTypes: array_values(array_unique($resourceTypes)),
            metadata: [
                'provider_available' => $status->available,
                'provider_status' => $status->status,
            ],
        );
    }
}
