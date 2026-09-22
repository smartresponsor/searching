<?php

declare(strict_types=1);

namespace App\Searching\Controller\Api;

use App\Searching\Contract\Bridge\SearchInterfacingBridgeProviderInterface;
use App\Searching\Service\Bridge\SearchBridgeConfigSerializer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Defines the search bridge api controller responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchBridgeApiController
{
    public function __construct(
        private SearchInterfacingBridgeProviderInterface $interfacingBridgeProvider,
        private SearchBridgeConfigSerializer $serializer,
    ) {
    }

    /**
     * Executes the interfacing responsibility defined by the Searching component contract.
     */
    #[Route('/api/search/bridge/interfacing', name: 'searching_api_bridge_interfacing', methods: ['GET'])]
    public function interfacing(): JsonResponse
    {
        return new JsonResponse($this->serializer->serializeConfig($this->interfacingBridgeProvider->getBridgeConfig()));
    }
}
