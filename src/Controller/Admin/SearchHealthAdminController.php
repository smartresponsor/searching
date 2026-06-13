<?php

declare(strict_types=1);

namespace App\Searching\Controller\Admin;

use App\Searching\Service\Serialization\SearchHealthReportSerializer;
use App\Searching\ServiceInterface\Health\SearchHealthCheckerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final readonly class SearchHealthAdminController
{
    public function __construct(
        private SearchHealthCheckerInterface $healthChecker,
        private SearchHealthReportSerializer $healthReportSerializer,
    ) {
    }

    #[Route('/admin/search/health', name: 'searching_admin_health', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        return new JsonResponse($this->healthReportSerializer->serializeReport(
            $this->healthChecker->check(),
        ));
    }
}
