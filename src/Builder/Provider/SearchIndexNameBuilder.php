<?php

declare(strict_types=1);

namespace App\Searching\Builder\Provider;

use App\Searching\Value\Document\SearchDocument;
use App\Searching\Value\Query\SearchQuery;
use App\Searching\Value\Query\SearchSuggestionQuery;

final class SearchIndexNameBuilder
{
    public function buildForDocument(SearchDocument $document, string $indexPrefix): string
    {
        return $this->join($indexPrefix, $document->component, $document->resourceType);
    }

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

    public function buildForParts(string $indexPrefix, string $component, string $resourceType): string
    {
        return $this->join($indexPrefix, $component, $resourceType);
    }

    public function buildDocumentId(SearchDocument $document): string
    {
        return $this->buildDocumentIdForParts($document->component, $document->resourceType, $document->resourceId);
    }

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
