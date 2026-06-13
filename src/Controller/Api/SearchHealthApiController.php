<?php

declare(strict_types=1);

namespace App\Searching\Controller\Api;

use App\Searching\Service\Serialization\SearchHealthReportSerializer;
use App\Searching\ServiceInterface\Health\SearchHealthCheckerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final readonly class SearchHealthApiController
{
    public function __construct(
        private SearchHealthCheckerInterface $healthChecker,
        private SearchHealthReportSerializer $healthReportSerializer,
    ) {
    }

    #[Route('/api/search/health', name: 'searching_api_health', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        $report = $this->healthChecker->check();
        $statusCode = 'unhealthy' === $report->status ? 503 : 200;

        return new JsonResponse($this->healthReportSerializer->serializeReport($report), $statusCode);
    }
}
