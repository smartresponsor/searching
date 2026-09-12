<?php

declare(strict_types=1);

namespace App\Searching\Controller\Admin;

use App\Searching\Contract\Query\SearchQueryLogReaderInterface;
use App\Searching\Service\Serialization\SearchQueryLogSerializer;
use App\Searching\Value\Query\SearchQueryLogCriteria;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class SearchQueryLogAdminController
{
    public function __construct(
        private SearchQueryLogReaderInterface $reader,
        private SearchQueryLogSerializer $serializer,
        private int $defaultLimit = 50,
    ) {
    }

    #[Route('/admin/search/query/log', name: 'searching_admin_query_logs', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $criteria = SearchQueryLogCriteria::fromArray($request->query->all(), $this->defaultLimit);

        return new JsonResponse([
            'items' => $this->serializer->serializeLogs($this->reader->recent($criteria)),
            'total' => $this->reader->count($criteria),
            'limit' => $criteria->limit,
            'offset' => $criteria->offset,
        ]);
    }
}
