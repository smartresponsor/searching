<?php

declare(strict_types=1);

namespace App\Searching\Controller\Admin;

use App\Searching\Service\Bridge\SearchBridgeSurfaceConfigSerializer;
use App\Searching\ServiceInterface\Bridge\InterfacingSearchBridgeProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final readonly class SearchBridgeAdminController
{
    public function __construct(
        private InterfacingSearchBridgeProviderInterface $interfacingBridgeProvider,
        private SearchBridgeSurfaceConfigSerializer $serializer,
    ) {
    }

    #[Route('/admin/search/bridge/interfacing', name: 'searching_admin_bridge_interfacing', methods: ['GET'])]
    public function interfacing(): JsonResponse
    {
        return new JsonResponse([
            'bridge' => 'interfacing',
            'surface' => $this->serializer->serializeConfig($this->interfacingBridgeProvider->getSurfaceConfig()),
        ]);
    }
}
