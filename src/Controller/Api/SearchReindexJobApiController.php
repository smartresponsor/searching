<?php

declare(strict_types=1);

namespace App\Searching\Controller\Api;

use App\Searching\Contract\Indexing\SearchReindexJobReaderInterface;
use App\Searching\Service\Serialization\SearchReindexJobSerializer;
use App\Searching\Value\Indexing\SearchReindexJobCriteria;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class SearchReindexJobApiController
{
    public function __construct(
        private SearchReindexJobReaderInterface $reader,
        private SearchReindexJobSerializer $serializer,
        private int $defaultLimit = 50,
    ) {
    }

    #[Route('/api/search/reindex/job', name: 'searching_api_reindex_jobs', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $criteria = SearchReindexJobCriteria::fromArray($request->query->all(), $this->defaultLimit);

        return new JsonResponse([
            'items' => $this->serializer->serializeList($this->reader->list($criteria)),
            'total' => $this->reader->count($criteria),
            'limit' => $criteria->limit,
            'offset' => $criteria->offset,
        ]);
    }

    #[Route('/api/search/reindex/job/{token}', name: 'searching_api_reindex_job_view', methods: ['GET'])]
    public function view(string $token): JsonResponse
    {
        $job = $this->reader->findOne($token);
        if (null === $job) {
            return new JsonResponse(['error' => 'search_reindex_job_not_found'], 404);
        }

        return new JsonResponse($this->serializer->serialize($job));
    }
}
