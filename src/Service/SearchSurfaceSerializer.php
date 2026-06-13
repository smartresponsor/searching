<?php

declare(strict_types=1);

namespace App\Searching\Service;

use App\Searching\Value\Surface\SearchSurfaceCapability;
use App\Searching\Value\Surface\SearchSurfaceFacet;
use App\Searching\Value\Surface\SearchSurfaceHighlight;
use App\Searching\Value\Surface\SearchSurfaceResult;
use App\Searching\Value\Surface\SearchSurfaceResultItem;
use App\Searching\Value\Surface\SearchSurfaceSuggestion;

final class SearchSurfaceSerializer
{
    /**
     * @return array<string, mixed>
     */
    public function serializeResult(SearchSurfaceResult $result): array
    {
        return [
            'query' => $result->query,
            'total' => $result->total,
            'page' => $result->page,
            'limit' => $result->limit,
            'items' => array_map($this->serializeItem(...), $result->items),
            'facets' => array_map($this->serializeFacet(...), $result->facets),
            'suggestions' => array_map($this->serializeSuggestion(...), $result->suggestions),
            'metadata' => $result->metadata,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function serializeItem(SearchSurfaceResultItem $item): array
    {
        return [
            'component' => $item->component,
            'resourceType' => $item->resourceType,
            'resourceId' => $item->resourceId,
            'title' => $item->title,
            'summary' => $item->summary,
            'routeName' => $item->routeName,
            'routeParameters' => $item->routeParameters,
            'score' => $item->score,
            'highlights' => array_map($this->serializeHighlight(...), $item->highlights),
            'metadata' => $item->metadata,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function serializeFacet(SearchSurfaceFacet $facet): array
    {
        return [
            'nameEntity' => $facet->nameEntity,
            'buckets' => $facet->buckets,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function serializeHighlight(SearchSurfaceHighlight $highlight): array
    {
        return [
            'field' => $highlight->field,
            'fragments' => $highlight->fragments,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function serializeSuggestion(SearchSurfaceSuggestion $suggestion): array
    {
        return array_filter([
            'text' => $suggestion->text,
            'score' => $suggestion->score,
            'component' => $suggestion->component,
            'resourceType' => $suggestion->resourceType,
            'resourceId' => $suggestion->resourceId,
            'routeName' => $suggestion->routeName,
            'routeParameters' => $suggestion->routeParameters,
            'metadata' => $suggestion->metadata,
        ], static fn (mixed $value): bool => null !== $value && [] !== $value);
    }

    /**
     * @return array<string, mixed>
     */
    public function serializeCapability(SearchSurfaceCapability $capability): array
    {
        return [
            'enabled' => $capability->enabled,
            'providerName' => $capability->providerName,
            'supportedFeatures' => $capability->supportedFeatures,
            'supportedComponents' => $capability->supportedComponents,
            'supportedResourceTypes' => $capability->supportedResourceTypes,
            'metadata' => $capability->metadata,
        ];
    }
}
