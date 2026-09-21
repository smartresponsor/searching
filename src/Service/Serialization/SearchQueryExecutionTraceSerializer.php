<?php

declare(strict_types=1);

namespace App\Searching\Service\Serialization;

use App\Searching\Value\Query\SearchQueryExecutionTrace;

final class SearchQueryExecutionTraceSerializer
{
    /**
     * @return array<string, mixed>
     */
    public function serialize(SearchQueryExecutionTrace $trace): array
    {
        return [
            'query' => $trace->query,
            'userId' => $trace->userId,
            'vendorId' => $trace->vendorId,
            'providerName' => $trace->providerName,
            'providerTotal' => $trace->providerTotal,
            'returnedTotal' => $trace->returnedTotal,
            'deniedCount' => $trace->deniedCount,
            'durationMs' => $trace->durationMs,
            'executedAt' => $trace->executedAt->format(\DateTimeInterface::ATOM),
            'successful' => $trace->successful,
            'errorClass' => $trace->errorClass,
            'errorMessage' => $trace->errorMessage,
            'providerMetadata' => $trace->providerMetadata,
            'metadata' => $trace->metadata,
            'executionContext' => $trace->executionContext?->toMetadata() ?? [],
        ];
    }
}
