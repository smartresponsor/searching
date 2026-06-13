<?php

declare(strict_types=1);

namespace App\Searching\Service;

use App\Searching\Value\Result\SearchFacet;
use App\Searching\Value\Result\SearchHighlight;
use App\Searching\Value\Result\SearchResult;
use App\Searching\Value\Result\SearchResultItem;
use App\Searching\Value\Result\SearchSuggestion;
use App\Searching\Value\Surface\SearchSurfaceFacet;
use App\Searching\Value\Surface\SearchSurfaceHighlight;
use App\Searching\Value\Surface\SearchSurfaceResult;
use App\Searching\Value\Surface\SearchSurfaceResultItem;
use App\Searching\Value\Surface\SearchSurfaceSuggestion;

final class SearchSurfaceMapper
{
    public function mapResult(SearchResult $result): SearchSurfaceResult
    {
        return new SearchSurfaceResult(
            query: $result->query,
            total: $result->total,
            page: $result->page,
            limit: $result->limit,
            items: array_map($this->mapItem(...), $result->items),
            facets: array_map($this->mapFacet(...), $result->facets),
            suggestions: array_map($this->mapSuggestion(...), $result->suggestions),
            metadata: $this->safeMetadata($result->metadata),
        );
    }

    public function mapItem(SearchResultItem $item): SearchSurfaceResultItem
    {
        return new SearchSurfaceResultItem(
            component: $item->component,
            resourceType: $item->resourceType,
            resourceId: $item->resourceId,
            title: $item->title,
            summary: $item->summary,
            routeName: $item->routeName,
            routeParameters: $item->routeParameters,
            score: $item->score,
            highlights: array_map($this->mapHighlight(...), $item->highlights),
            metadata: $this->safeMetadata($item->metadata),
        );
    }

    public function mapFacet(SearchFacet $facet): SearchSurfaceFacet
    {
        return new SearchSurfaceFacet($facet->nameEntity, $facet->buckets);
    }

    public function mapHighlight(SearchHighlight $highlight): SearchSurfaceHighlight
    {
        return new SearchSurfaceHighlight($highlight->field, $highlight->fragments);
    }

    public function mapSuggestion(SearchSuggestion $suggestion): SearchSurfaceSuggestion
    {
        return new SearchSurfaceSuggestion(
            text: $suggestion->text,
            score: $suggestion->score,
            component: $suggestion->component,
            resourceType: $suggestion->resourceType,
            resourceId: $suggestion->resourceId,
            routeName: is_string($suggestion->metadata['routeName'] ?? null) ? $suggestion->metadata['routeName'] : null,
            routeParameters: is_array($suggestion->metadata['routeParameters'] ?? null) ? $suggestion->metadata['routeParameters'] : [],
            metadata: $this->safeMetadata($suggestion->metadata),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function safeMetadata(array $metadata): array
    {
        unset(
            $metadata['backend_payload'],
            $metadata['provider_payload'],
            $metadata['raw_hit'],
            $metadata['raw_response'],
            $metadata['_source']
        );

        return $metadata;
    }
}
