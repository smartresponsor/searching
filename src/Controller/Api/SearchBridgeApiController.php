<?php

declare(strict_types=1);

namespace App\Searching\Controller\Api;

use App\Searching\Service\Bridge\SearchBridgeSurfaceConfigSerializer;
use App\Searching\ServiceInterface\Bridge\InterfacingSearchBridgeProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final readonly class SearchBridgeApiController
{
    public function __construct(
        private InterfacingSearchBridgeProviderInterface $interfacingBridgeProvider,
        private SearchBridgeSurfaceConfigSerializer $serializer,
    ) {
    }

    #[Route('/api/search/bridge/interfacing', name: 'searching_api_bridge_interfacing', methods: ['GET'])]
    public function interfacing(): JsonResponse
    {
        return new JsonResponse($this->serializer->serializeConfig($this->interfacingBridgeProvider->getSurfaceConfig()));
    }
}
