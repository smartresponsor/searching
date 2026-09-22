<?php

declare(strict_types=1);

namespace App\Searching\Controller\Api;

use App\Searching\Contract\Indexing\SearchIndexedResourceReaderInterface;
use App\Searching\Service\Serialization\SearchIndexedResourceSerializer;
use App\Searching\ValueObject\Indexing\SearchIndexedResourceCriteria;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class SearchIndexedResourceApiController
{
    public function __construct(
        private SearchIndexedResourceReaderInterface $reader,
        private SearchIndexedResourceSerializer $serializer,
        private int $defaultLimit = 50,
    ) {
    }

    #[Route('/api/search/indexed/resource', name: 'searching_api_indexed_resources', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $criteria = SearchIndexedResourceCriteria::fromArray($request->query->all(), $this->defaultLimit);

        return new JsonResponse([
            'items' => $this->serializer->serializeList($this->reader->list($criteria)),
            'total' => $this->reader->count($criteria),
            'limit' => $criteria->limit,
            'offset' => $criteria->offset,
        ]);
    }
}
