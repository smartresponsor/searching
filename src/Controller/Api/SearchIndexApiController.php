<?php

declare(strict_types=1);

namespace App\Searching\Controller\Api;

use App\Searching\Contract\Indexing\SearchIndexReaderInterface;
use App\Searching\Contract\Indexing\SearchIndexWriterInterface;
use App\Searching\Service\Serialization\SearchIndexSerializer;
use App\Searching\ValueObject\Indexing\SearchIndexCriteria;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Defines the search index api controller responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchIndexApiController
{
    public function __construct(
        private SearchIndexReaderInterface $reader,
        private SearchIndexWriterInterface $writer,
        private SearchIndexSerializer $serializer,
        private int $defaultLimit = 50,
    ) {
    }

    /**
     * Lists the list exposed through the Searching component read boundary.
     */
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

    /**
     * Creates the create required by the Searching component runtime flow.
     */
    #[Route('/api/search/index', name: 'searching_api_indexes_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $index = $this->writer->upsert($this->jsonPayload($request));

        return new JsonResponse($this->serializer->serialize($index), 201);
    }

    /**
     * Executes the update responsibility defined by the Searching component contract.
     */
    #[Route('/api/search/index/{token}', name: 'searching_api_indexes_update', methods: ['PATCH'])]
    public function update(string $token, Request $request): JsonResponse
    {
        $payload = $this->jsonPayload($request);
        $provider = $this->requestValue($request, $payload, 'provider');
        $component = $this->requestValue($request, $payload, 'component');
        if ('' === $provider || '' === $component) {
            return new JsonResponse(['error' => 'provider and component are required'], 400);
        }

        $index = $this->reader->findOne($provider, $component, $token);
        if (null === $index) {
            return new JsonResponse(['error' => 'search_index_not_found'], 404);
        }

        return new JsonResponse($this->serializer->serialize($this->writer->update($index, $this->jsonPayload($request))));
    }

    /**
     * Deletes the delete through the Searching component mutation boundary.
     */
    #[Route('/api/search/index/{token}', name: 'searching_api_indexes_delete', methods: ['DELETE'])]
    public function delete(string $token, Request $request): JsonResponse
    {
        $payload = $this->jsonPayload($request);
        $provider = $this->requestValue($request, $payload, 'provider');
        $component = $this->requestValue($request, $payload, 'component');
        if ('' === $provider || '' === $component) {
            return new JsonResponse(['deleted' => false, 'error' => 'provider and component are required'], 400);
        }

        $index = $this->reader->findOne($provider, $component, $token);
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
        $value = $request->query->get($key);
        if (null === $value) {
            $value = $payload[$key] ?? '';
        }

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
        if (!is_array($decoded)) {
            return [];
        }

        /** @var array<string, mixed> $decoded */
        return $decoded;
    }
}
