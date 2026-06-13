<?php

declare(strict_types=1);

namespace App\Searching\Service;

use App\Searching\ServiceInterface\Provider\SearchProviderInterface;
use App\Searching\ServiceInterface\Query\SearchQueryExecutorInterface;
use App\Searching\ServiceInterface\Query\SearchSuggestionProviderInterface;
use App\Searching\ServiceInterface\Registry\SearchableResourceRegistryInterface;
use App\Searching\ServiceInterface\Surface\SearchSuggestionSurfaceProviderInterface;
use App\Searching\ServiceInterface\Surface\SearchSurfaceProviderInterface;
use App\Searching\Value\Surface\SearchSurfaceCapability;
use App\Searching\Value\Surface\SearchSurfaceQuery;
use App\Searching\Value\Surface\SearchSurfaceResult;
use App\Searching\Value\Surface\SearchSurfaceSuggestionQuery;

final readonly class SearchingSearchSurfaceProvider implements SearchSurfaceProviderInterface, SearchSuggestionSurfaceProviderInterface
{
    public function __construct(
        private SearchQueryExecutorInterface $queryExecutor,
        private SearchSuggestionProviderInterface $suggestionProvider,
        private SearchProviderInterface $searchProvider,
        private SearchableResourceRegistryInterface $resourceRegistry,
        private SearchSurfaceMapper $surfaceMapper,
    ) {
    }

    public function search(SearchSurfaceQuery $query): SearchSurfaceResult
    {
        return $this->surfaceMapper->mapResult($this->queryExecutor->execute($query->toInternalQuery()));
    }

    public function suggest(SearchSurfaceSuggestionQuery $query): array
    {
        return array_map(
            $this->surfaceMapper->mapSuggestion(...),
            $this->suggestionProvider->suggestByQuery($query->toInternalQuery()),
        );
    }

    public function getCapability(): SearchSurfaceCapability
    {
        $status = $this->searchProvider->getStatus();
        $resources = $this->resourceRegistry->definitions();

        $components = [];
        $resourceTypes = [];

        foreach ($resources as $resource) {
            $components[] = $resource->component;
            $resourceTypes[] = $resource->resourceType;
        }

        return new SearchSurfaceCapability(
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
