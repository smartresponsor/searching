<?php

declare(strict_types=1);

namespace App\Searching\Controller\Api;

use App\Searching\Contract\Registry\SearchableResourceRegistryInterface;
use App\Searching\Service\Serialization\SearchRegistrySerializer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Defines the search resource api controller responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchResourceApiController
{
    public function __construct(
        private SearchableResourceRegistryInterface $resourceRegistry,
        private SearchRegistrySerializer $registrySerializer,
    ) {
    }

    #[Route('/api/search/resource', name: 'searching_api_resources', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        return new JsonResponse($this->registrySerializer->serializeDefinitions(
            $this->resourceRegistry->definitions(),
        ));
    }
}
