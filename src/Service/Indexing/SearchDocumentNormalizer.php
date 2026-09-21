<?php

declare(strict_types=1);

namespace App\Searching\Service\Indexing;

use App\Searching\Value\Document\SearchDocument;

final class SearchDocumentNormalizer
{
    public function normalize(SearchDocument $document): SearchDocument
    {
        return new SearchDocument(
            component: strtolower(trim($document->component)),
            resourceType: strtolower(trim($document->resourceType)),
            resourceId: trim($document->resourceId),
            title: trim($document->title),
            summary: null !== $document->summary ? trim($document->summary) : null,
            body: null !== $document->body ? trim($document->body) : null,
            keywords: array_values(array_unique(array_map('strval', $document->keywords))),
            facets: $document->facets,
            permissions: array_values(array_unique(array_map('strval', $document->permissions))),
            locale: $document->locale,
            vendorId: $document->vendorId,
            ownerId: $document->ownerId,
            routeName: trim($document->routeName),
            routeParameters: $document->routeParameters,
            updatedAt: $document->updatedAt,
        );
    }
}
