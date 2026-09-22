<?php

declare(strict_types=1);

namespace App\Searching\Service\Provider;

use App\Searching\ValueObject\Document\SearchDocument;

final class SearchDocumentPayloadMapper
{
    /**
     * @return array<string, mixed>
     */
    public function map(SearchDocument $document): array
    {
        return [
            'component' => $document->component,
            'resource_type' => $document->resourceType,
            'resource_id' => $document->resourceId,
            'title' => $document->title,
            'summary' => $document->summary,
            'body' => $document->body,
            'keywords' => $document->keywords,
            'facets' => $document->facets,
            'permissions' => $document->permissions,
            'locale' => $document->locale,
            'vendor_id' => $document->vendorId,
            'owner_id' => $document->ownerId,
            'route_name' => $document->routeName,
            'route_parameters' => $document->routeParameters,
            'updated_at' => $document->updatedAt->format(DATE_ATOM),
        ];
    }
}
