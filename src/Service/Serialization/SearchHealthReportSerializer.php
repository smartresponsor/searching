<?php

declare(strict_types=1);

namespace App\Searching\Service\Serialization;

use App\Searching\ValueObject\Health\SearchHealthIndicator;
use App\Searching\ValueObject\Health\SearchHealthReport;

/**
 * Defines the search health report serializer responsibility within the Searching component runtime and its typed boundaries.
 */
final class SearchHealthReportSerializer
{
    /**
     * @return array<string, mixed>
     */
    public function serializeReport(SearchHealthReport $report): array
    {
        return [
            'status' => $report->status,
            'ready' => $report->isReady(),
            'checkedAt' => $report->checkedAt->format(DATE_ATOM),
            'indicators' => array_map($this->serializeIndicator(...), $report->indicators),
            'metadata' => $report->metadata,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function serializeIndicator(SearchHealthIndicator $indicator): array
    {
        return [
            'nameEntity' => $indicator->nameEntity,
            'status' => $indicator->status,
            'summary' => $indicator->summary,
            'metrics' => $indicator->metrics,
            'metadata' => $indicator->metadata,
        ];
    }
}
