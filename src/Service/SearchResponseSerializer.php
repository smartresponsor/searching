<?php

declare(strict_types=1);

namespace App\Searching\Service;

use App\Searching\Value\Provider\SearchCapability;
use App\Searching\Value\Result\SearchFacetResponse;
use App\Searching\Value\Result\SearchHighlightResponse;
use App\Searching\Value\Result\SearchResponse;
use App\Searching\Value\Result\SearchResponseItem;
use App\Searching\Value\Result\SearchSuggestionResponse;

final class SearchResponseSerializer
{
    /**
     * @return array<string, mixed>
     */
    public function serializeResult(SearchResponse $result): array
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
    public function serializeItem(SearchResponseItem $item): array
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
    public function serializeFacet(SearchFacetResponse $facet): array
    {
        return [
            'identifier' => $facet->identifier,
            'nameEntity' => $facet->nameEntity,
            'buckets' => $facet->buckets,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function serializeHighlight(SearchHighlightResponse $highlight): array
    {
        return [
            'field' => $highlight->field,
            'fragments' => $highlight->fragments,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function serializeSuggestion(SearchSuggestionResponse $suggestion): array
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
    public function serializeCapability(SearchCapability $capability): array
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
