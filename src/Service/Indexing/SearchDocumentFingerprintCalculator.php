<?php

declare(strict_types=1);

namespace App\Searching\Service\Indexing;

use App\Searching\Value\Document\SearchDocument;
use App\Searching\Value\Indexing\SearchDocumentFingerprint;

final class SearchDocumentFingerprintCalculator
{
    public function fingerprint(SearchDocument $document): SearchDocumentFingerprint
    {
        $payload = [
            'component' => $document->component,
            'resourceType' => $document->resourceType,
            'resourceId' => $document->resourceId,
            'title' => $document->title,
            'summary' => $document->summary,
            'body' => $document->body,
            'keywords' => $this->normalizeArray($document->keywords),
            'facets' => $this->normalizeArray($document->facets),
            'permissions' => $this->normalizeArray($document->permissions),
            'locale' => $document->locale,
            'vendorId' => $document->vendorId,
            'ownerId' => $document->ownerId,
            'routeName' => $document->routeName,
            'routeParameters' => $this->normalizeArray($document->routeParameters),
            'updatedAt' => $document->updatedAt->format(\DateTimeInterface::ATOM),
        ];

        return new SearchDocumentFingerprint(
            component: $document->component,
            resourceType: $document->resourceType,
            resourceId: $document->resourceId,
            documentHash: hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR)),
            sourceUpdatedAt: $document->updatedAt,
        );
    }

    /**
     * @param array<mixed> $value
     *
     * @return array<mixed>
     */
    private function normalizeArray(array $value): array
    {
        foreach ($value as $key => $item) {
            if (is_array($item)) {
                $value[$key] = $this->normalizeArray($item);
            }
        }

        if (!array_is_list($value)) {
            ksort($value);
        }

        return $value;
    }
}
