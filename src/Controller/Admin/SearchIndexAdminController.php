<?php

declare(strict_types=1);

namespace App\Searching\Controller\Admin;

use App\Searching\Service\Provider\SearchProviderStatusCollector;
use App\Searching\Service\Serialization\SearchIndexSerializer;
use App\Searching\Service\Serialization\SearchProviderStatusSerializer;
use App\Searching\Service\Serialization\SearchRegistrySerializer;
use App\Searching\ServiceInterface\Indexing\SearchIndexReaderInterface;
use App\Searching\ServiceInterface\Registry\SearchableResourceRegistryInterface;
use App\Searching\Value\Indexing\SearchIndexCriteria;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class SearchIndexAdminController
{
    public function __construct(
        private SearchIndexReaderInterface $indexReader,
        private SearchIndexSerializer $indexSerializer,
        private SearchableResourceRegistryInterface $resourceRegistry,
        private SearchProviderStatusCollector $statusCollector,
        private SearchRegistrySerializer $registrySerializer,
        private SearchProviderStatusSerializer $statusSerializer,
        private int $defaultLimit = 50,
    ) {
    }

    #[Route('/admin/search/indexes', name: 'searching_admin_indexes', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $criteria = SearchIndexCriteria::fromArray($request->query->all(), $this->defaultLimit);

        return new JsonResponse([
            'items' => $this->indexSerializer->serializeList($this->indexReader->list($criteria)),
            'total' => $this->indexReader->count($criteria),
            'limit' => $criteria->limit,
            'offset' => $criteria->offset,
            'resources' => $this->registrySerializer->serializeDefinitions($this->resourceRegistry->definitions()),
            'providers' => $this->statusSerializer->serializeStatuses($this->statusCollector->collect()),
        ]);
    }
}
