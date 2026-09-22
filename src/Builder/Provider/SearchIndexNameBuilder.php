<?php

declare(strict_types=1);

namespace App\Searching\Builder\Provider;

use App\Searching\ValueObject\Document\SearchDocument;
use App\Searching\ValueObject\Query\SearchQuery;
use App\Searching\ValueObject\Query\SearchSuggestionQuery;

/**
 * Defines the search index name builder responsibility within the Searching component runtime and its typed boundaries.
 */
final class SearchIndexNameBuilder
{
    /**
     * Builds the for document used by the Searching component execution and integration boundaries.
     */
    public function buildForDocument(SearchDocument $document, string $indexPrefix): string
    {
        return $this->join($indexPrefix, $document->component, $document->resourceType);
    }

    /**
     * Builds the for query used by the Searching component execution and integration boundaries.
     */
    public function buildForQuery(SearchQuery $query, string $indexPrefix): string
    {
        if (1 === count($query->components) && 1 === count($query->resourceTypes)) {
            return $this->join($indexPrefix, $query->components[0], $query->resourceTypes[0]);
        }

        if (1 === count($query->components) && 0 === count($query->resourceTypes)) {
            return $this->join($indexPrefix, $query->components[0], 'all');
        }

        return $this->join($indexPrefix, 'all');
    }

    /**
     * Builds the for suggestion used by the Searching component execution and integration boundaries.
     */
    public function buildForSuggestion(SearchSuggestionQuery $query, string $indexPrefix): string
    {
        if (1 === count($query->components) && 1 === count($query->resourceTypes)) {
            return $this->join($indexPrefix, $query->components[0], $query->resourceTypes[0]);
        }

        if (1 === count($query->components) && 0 === count($query->resourceTypes)) {
            return $this->join($indexPrefix, $query->components[0], 'all');
        }

        return $this->join($indexPrefix, 'all');
    }

    /**
     * Builds the for parts used by the Searching component execution and integration boundaries.
     */
    public function buildForParts(string $indexPrefix, string $component, string $resourceType): string
    {
        return $this->join($indexPrefix, $component, $resourceType);
    }

    /**
     * Builds the document id used by the Searching component execution and integration boundaries.
     */
    public function buildDocumentId(SearchDocument $document): string
    {
        return $this->buildDocumentIdForParts($document->component, $document->resourceType, $document->resourceId);
    }

    /**
     * Builds the document id for parts used by the Searching component execution and integration boundaries.
     */
    public function buildDocumentIdForParts(string $component, string $resourceType, string $resourceId): string
    {
        return $this->normalize($component).'_'.$this->normalize($resourceType).'_'.$this->normalize($resourceId);
    }

    private function join(string ...$parts): string
    {
        return implode('_', array_map($this->normalize(...), array_filter($parts, static fn (string $part): bool => '' !== $part)));
    }

    private function normalize(string $value): string
    {
        $normalized = strtolower(trim($value));
        $normalized = (string) preg_replace('/[^a-z0-9]+/', '_', $normalized);
        $normalized = trim($normalized, '_');

        return '' !== $normalized ? $normalized : 'default';
    }
}
