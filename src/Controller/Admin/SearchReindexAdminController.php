<?php

declare(strict_types=1);

namespace App\Searching\Controller\Admin;

use App\Searching\Contract\Indexing\SearchReindexCoordinatorInterface;
use App\Searching\Service\Serialization\SearchReindexResultSerializer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Defines the search reindex admin controller responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchReindexAdminController
{
    public function __construct(
        private SearchReindexCoordinatorInterface $reindexCoordinator,
        private SearchReindexResultSerializer $reindexResultSerializer,
    ) {
    }

    #[Route('/admin/search/reindex', name: 'searching_admin_reindex', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $component = $request->request->getString('component') ?: null;
        $resourceType = $request->request->getString('resourceType') ?: null;
        $changedSinceInput = $request->request->getString('changedSince');
        $changedSince = '' === $changedSinceInput ? null : new \DateTimeImmutable($changedSinceInput);

        return new JsonResponse($this->reindexResultSerializer->serialize(
            $this->reindexCoordinator->reindex($component, $resourceType, $changedSince),
        ));
    }
}
