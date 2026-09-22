<?php

declare(strict_types=1);

namespace App\Searching\Controller\Api;

use App\Searching\Contract\Tuning\SearchSynonymReaderInterface;
use App\Searching\Contract\Tuning\SearchSynonymWriterInterface;
use App\Searching\Service\Serialization\SearchSynonymSerializer;
use App\Searching\ValueObject\Tuning\SearchSynonymCriteria;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class SearchSynonymApiController
{
    public function __construct(
        private SearchSynonymReaderInterface $reader,
        private SearchSynonymWriterInterface $writer,
        private SearchSynonymSerializer $serializer,
        private int $defaultLimit = 50,
    ) {
    }

    #[Route('/api/search/synonym', name: 'searching_api_synonyms_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $criteria = SearchSynonymCriteria::fromArray($request->query->all(), $this->defaultLimit);

        return new JsonResponse([
            'items' => $this->serializer->serializeSynonyms($this->reader->find($criteria)),
            'total' => $this->reader->count($criteria),
            'limit' => $criteria->limit,
            'offset' => $criteria->offset,
        ]);
    }

    #[Route('/api/search/synonym', name: 'searching_api_synonyms_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $synonym = $this->writer->create($this->payload($request));

        return new JsonResponse($this->serializer->serialize($synonym), JsonResponse::HTTP_CREATED);
    }

    #[Route('/api/search/synonym/{id}', name: 'searching_api_synonyms_update', requirements: ['id' => '\\d+'], methods: ['PATCH', 'PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $synonym = $this->reader->findOne($id);
        if (null === $synonym) {
            return new JsonResponse(['error' => 'Search synonym was not found.'], JsonResponse::HTTP_NOT_FOUND);
        }

        $synonym = $this->writer->update($synonym, $this->payload($request));

        return new JsonResponse($this->serializer->serialize($synonym));
    }

    #[Route('/api/search/synonym/{id}', name: 'searching_api_synonyms_delete', requirements: ['id' => '\\d+'], methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $synonym = $this->reader->findOne($id);
        if (null === $synonym) {
            return new JsonResponse(['error' => 'Search synonym was not found.'], JsonResponse::HTTP_NOT_FOUND);
        }

        $this->writer->delete($synonym);

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Request $request): array
    {
        $decoded = json_decode($request->getContent() ?: '{}', true);
        if (!is_array($decoded)) {
            return [];
        }

        /** @var array<string, mixed> $decoded */
        return $decoded;
    }
}
