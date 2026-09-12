<?php

declare(strict_types=1);

namespace App\Searching\Service;

use App\Searching\Value\Result\SearchFacet;
use App\Searching\Value\Result\SearchFacetResponse;
use App\Searching\Value\Result\SearchHighlight;
use App\Searching\Value\Result\SearchHighlightResponse;
use App\Searching\Value\Result\SearchResponse;
use App\Searching\Value\Result\SearchResponseItem;
use App\Searching\Value\Result\SearchResult;
use App\Searching\Value\Result\SearchResultItem;
use App\Searching\Value\Result\SearchSuggestion;
use App\Searching\Value\Result\SearchSuggestionResponse;

final class SearchResponseMapper
{
    public function mapResult(SearchResult $result): SearchResponse
    {
        return new SearchResponse(
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

    public function mapItem(SearchResultItem $item): SearchResponseItem
    {
        return new SearchResponseItem(
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

    public function mapFacet(SearchFacet $facet): SearchFacetResponse
    {
        return new SearchFacetResponse($facet->nameEntity, $facet->buckets);
    }

    public function mapHighlight(SearchHighlight $highlight): SearchHighlightResponse
    {
        return new SearchHighlightResponse($highlight->field, $highlight->fragments);
    }

    public function mapSuggestion(SearchSuggestion $suggestion): SearchSuggestionResponse
    {
        $routeParameters = $suggestion->metadata['routeParameters'] ?? null;
        if (!is_array($routeParameters)) {
            $routeParameters = [];
        }
        /** @var array<string, mixed> $routeParameters */

        return new SearchSuggestionResponse(
            text: $suggestion->text,
            score: $suggestion->score,
            component: $suggestion->component,
            resourceType: $suggestion->resourceType,
            resourceId: $suggestion->resourceId,
            routeName: is_string($suggestion->metadata['routeName'] ?? null) ? $suggestion->metadata['routeName'] : null,
            routeParameters: $routeParameters,
            metadata: $this->safeMetadata($suggestion->metadata),
        );
    }

    /**
     * @param array<string, mixed> $metadata
     *
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
