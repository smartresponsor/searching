<?php

declare(strict_types=1);

namespace App\Searching\Service\Serialization;

use App\Searching\ValueObject\Result\SearchFacet;
use App\Searching\ValueObject\Result\SearchHighlight;
use App\Searching\ValueObject\Result\SearchResult;
use App\Searching\ValueObject\Result\SearchResultItem;
use App\Searching\ValueObject\Result\SearchSuggestion;

final class SearchResultSerializer
{
    /**
     * @return array<string, mixed>
     */
    public function serialize(SearchResult $result): array
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
    public function serializeItem(SearchResultItem $item): array
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
    public function serializeFacet(SearchFacet $facet): array
    {
        return [
            'nameEntity' => $facet->nameEntity,
            'buckets' => $facet->buckets,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function serializeHighlight(SearchHighlight $highlight): array
    {
        return [
            'field' => $highlight->field,
            'fragments' => $highlight->fragments,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function serializeSuggestion(SearchSuggestion $suggestion): array
    {
        return array_filter([
            'text' => $suggestion->text,
            'score' => $suggestion->score,
            'component' => $suggestion->component,
            'resourceType' => $suggestion->resourceType,
            'resourceId' => $suggestion->resourceId,
            'metadata' => $suggestion->metadata,
        ], static fn (mixed $value): bool => null !== $value && [] !== $value);
    }
}
