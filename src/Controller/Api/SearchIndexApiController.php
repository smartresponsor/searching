<?php

declare(strict_types=1);

namespace App\Searching\Controller\Api;

use App\Searching\Service\Serialization\SearchIndexSerializer;
use App\Searching\ServiceInterface\Indexing\SearchIndexReaderInterface;
use App\Searching\ServiceInterface\Indexing\SearchIndexWriterInterface;
use App\Searching\Value\Indexing\SearchIndexCriteria;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class SearchIndexApiController
{
    public function __construct(
        private SearchIndexReaderInterface $reader,
        private SearchIndexWriterInterface $writer,
        private SearchIndexSerializer $serializer,
        private int $defaultLimit = 50,
    ) {
    }

    #[Route('/api/search/index', name: 'searching_api_indexes', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $criteria = SearchIndexCriteria::fromArray($request->query->all(), $this->defaultLimit);

        return new JsonResponse([
            'items' => $this->serializer->serializeList($this->reader->list($criteria)),
            'total' => $this->reader->count($criteria),
            'limit' => $criteria->limit,
            'offset' => $criteria->offset,
        ]);
    }

    #[Route('/api/search/index', name: 'searching_api_indexes_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $index = $this->writer->upsert($this->jsonPayload($request));

        return new JsonResponse($this->serializer->serialize($index), 201);
    }

    #[Route('/api/search/index/{resourceType}', name: 'searching_api_indexes_update', methods: ['PATCH'])]
    public function update(string $resourceType, Request $request): JsonResponse
    {
        $payload = $this->jsonPayload($request);
        $provider = $this->requestValue($request, $payload, 'provider');
        $component = $this->requestValue($request, $payload, 'component');
        if ('' === $provider || '' === $component) {
            return new JsonResponse(['error' => 'provider and component are required'], 400);
        }

        $index = $this->reader->findOne($provider, $component, $resourceType);
        if (null === $index) {
            return new JsonResponse(['error' => 'search_index_not_found'], 404);
        }

        return new JsonResponse($this->serializer->serialize($this->writer->update($index, $this->jsonPayload($request))));
    }

    #[Route('/api/search/index/{resourceType}', name: 'searching_api_indexes_delete', methods: ['DELETE'])]
    public function delete(string $resourceType, Request $request): JsonResponse
    {
        $payload = $this->jsonPayload($request);
        $provider = $this->requestValue($request, $payload, 'provider');
        $component = $this->requestValue($request, $payload, 'component');
        if ('' === $provider || '' === $component) {
            return new JsonResponse(['deleted' => false, 'error' => 'provider and component are required'], 400);
        }

        $index = $this->reader->findOne($provider, $component, $resourceType);
        if (null === $index) {
            return new JsonResponse(['deleted' => false, 'error' => 'search_index_not_found'], 404);
        }

        $this->writer->delete($index);

        return new JsonResponse(['deleted' => true]);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function requestValue(Request $request, array $payload, string $key): string
    {
        $value = $request->query->get($key, $payload[$key] ?? '');

        return is_scalar($value) ? trim((string) $value) : '';
    }

    /**
     * @return array<string, mixed>
     */
    private function jsonPayload(Request $request): array
    {
        $content = trim($request->getContent());
        if ('' === $content) {
            return $request->request->all();
        }

        $decoded = json_decode($content, true);

        return is_array($decoded) ? $decoded : [];
    }
}
