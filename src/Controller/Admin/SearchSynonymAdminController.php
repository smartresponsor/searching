<?php

declare(strict_types=1);

namespace App\Searching\Controller\Admin;

use App\Searching\Service\Serialization\SearchSynonymSerializer;
use App\Searching\ServiceInterface\Tuning\SearchSynonymReaderInterface;
use App\Searching\Value\Tuning\SearchSynonymCriteria;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class SearchSynonymAdminController
{
    public function __construct(
        private SearchSynonymReaderInterface $reader,
        private SearchSynonymSerializer $serializer,
        private int $defaultLimit = 50,
    ) {
    }

    #[Route('/admin/search/synonyms', name: 'searching_admin_synonyms', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $criteria = SearchSynonymCriteria::fromArray($request->query->all(), $this->defaultLimit);

        return new JsonResponse([
            'items' => $this->serializer->serializeSynonyms($this->reader->find($criteria)),
            'total' => $this->reader->count($criteria),
            'limit' => $criteria->limit,
            'offset' => $criteria->offset,
        ]);
    }
}
