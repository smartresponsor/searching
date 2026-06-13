<?php

declare(strict_types=1);

namespace App\Searching\Controller\Admin;

use App\Searching\Service\Serialization\SearchRelevanceProfileSerializer;
use App\Searching\ServiceInterface\Tuning\SearchRelevanceProfileReaderInterface;
use App\Searching\Value\Tuning\SearchRelevanceProfileCriteria;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class SearchRelevanceProfileAdminController
{
    public function __construct(
        private SearchRelevanceProfileReaderInterface $reader,
        private SearchRelevanceProfileSerializer $serializer,
        private int $defaultLimit = 50,
    ) {
    }

    #[Route('/admin/search/relevance-profiles', name: 'searching_admin_relevance_profiles', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $criteria = SearchRelevanceProfileCriteria::fromArray($request->query->all(), $this->defaultLimit);

        return new JsonResponse([
            'items' => $this->serializer->serializeProfiles($this->reader->find($criteria)),
            'total' => $this->reader->count($criteria),
            'limit' => $criteria->limit,
            'offset' => $criteria->offset,
        ]);
    }
}
