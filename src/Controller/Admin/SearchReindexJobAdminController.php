<?php

declare(strict_types=1);

namespace App\Searching\Controller\Admin;

use App\Searching\Contract\Indexing\SearchReindexJobReaderInterface;
use App\Searching\Service\Serialization\SearchReindexJobSerializer;
use App\Searching\ValueObject\Indexing\SearchReindexJobCriteria;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Defines the search reindex job admin controller responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchReindexJobAdminController
{
    public function __construct(
        private SearchReindexJobReaderInterface $reader,
        private SearchReindexJobSerializer $serializer,
        private int $defaultLimit = 50,
    ) {
    }

    /**
     * Lists the list exposed through the Searching component read boundary.
     */
    #[Route('/admin/search/reindex/job', name: 'searching_admin_reindex_jobs', methods: ['GET'])]
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
}
