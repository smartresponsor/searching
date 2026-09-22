<?php

declare(strict_types=1);

namespace App\Searching\Controller\Api;

use App\Searching\Contract\Tuning\SearchRelevanceProfileReaderInterface;
use App\Searching\Contract\Tuning\SearchRelevanceProfileWriterInterface;
use App\Searching\Service\Serialization\SearchRelevanceProfileSerializer;
use App\Searching\ValueObject\Tuning\SearchRelevanceProfileCriteria;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class SearchRelevanceProfileApiController
{
    public function __construct(
        private SearchRelevanceProfileReaderInterface $reader,
        private SearchRelevanceProfileWriterInterface $writer,
        private SearchRelevanceProfileSerializer $serializer,
        private int $defaultLimit = 50,
    ) {
    }

    #[Route('/api/search/relevance/profile', name: 'searching_api_relevance_profiles_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $criteria = SearchRelevanceProfileCriteria::fromArray($request->query->all(), $this->defaultLimit);

        return new JsonResponse([
            'items' => $this->serializer->serializeProfiles($this->reader->find($criteria)),
            'total' => $this->reader->count($criteria),
            'limit' => $criteria->limit,
            'offset' => $criteria->offset,
        ]);
    }

    #[Route('/api/search/relevance/profile', name: 'searching_api_relevance_profiles_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $profile = $this->writer->create($this->payload($request));

        return new JsonResponse($this->serializer->serialize($profile), JsonResponse::HTTP_CREATED);
    }

    #[Route('/api/search/relevance/profile/{id}', name: 'searching_api_relevance_profiles_update', requirements: ['id' => '\\d+'], methods: ['PATCH', 'PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $profile = $this->reader->findOne($id);
        if (null === $profile) {
            return new JsonResponse(['error' => 'Search relevance profile was not found.'], JsonResponse::HTTP_NOT_FOUND);
        }

        $profile = $this->writer->update($profile, $this->payload($request));

        return new JsonResponse($this->serializer->serialize($profile));
    }

    #[Route('/api/search/relevance/profile/{id}', name: 'searching_api_relevance_profiles_delete', requirements: ['id' => '\\d+'], methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $profile = $this->reader->findOne($id);
        if (null === $profile) {
            return new JsonResponse(['error' => 'Search relevance profile was not found.'], JsonResponse::HTTP_NOT_FOUND);
        }

        $this->writer->delete($profile);

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
