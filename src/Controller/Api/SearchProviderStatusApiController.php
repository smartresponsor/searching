<?php

declare(strict_types=1);

namespace App\Searching\Controller\Api;

use App\Searching\Service\Provider\SearchProviderStatusCollector;
use App\Searching\Service\Serialization\SearchProviderStatusSerializer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Defines the search provider status api controller responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchProviderStatusApiController
{
    public function __construct(
        private SearchProviderStatusCollector $statusCollector,
        private SearchProviderStatusSerializer $statusSerializer,
    ) {
    }

    #[Route('/api/search/provider/status', name: 'searching_api_provider_status', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        return new JsonResponse($this->statusSerializer->serializeStatuses(
            $this->statusCollector->collect(),
        ));
    }
}
