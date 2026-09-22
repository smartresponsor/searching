<?php

declare(strict_types=1);

namespace App\Searching\Service\Flow;

use App\Searching\Contract\Flow\SearchOperationLimiterInterface;
use App\Searching\ValueObject\Flow\SearchOperationLimitDecision;
use App\Searching\ValueObject\Flow\SearchOperationLimitRequest;

/**
 * Defines the search operation limiter responsibility within the Searching component runtime and its typed boundaries.
 */
final class SearchOperationLimiter implements SearchOperationLimiterInterface
{
    /** @var array<string, array{window_start:int, used:int}> */
    private array $buckets = [];

    /**
     * @param array<string, array{limit:int, window_seconds:int, mode:string}> $operationLimits
     */
    public function __construct(
        private readonly array $operationLimits = [],
        private readonly string $defaultMode = 'reject',
    ) {
    }

    /**
     * Executes the decide responsibility defined by the Searching component contract.
     */
    public function decide(SearchOperationLimitRequest $request): SearchOperationLimitDecision
    {
        $limit = $this->operationLimits[$request->operation] ?? null;

        if (!is_array($limit)) {
            return SearchOperationLimitDecision::allow([
                'limiter' => 'in_memory',
                'operation' => $request->operation,
                'identity' => $request->identity,
                'configured' => false,
            ]);
        }

        $max = max(1, $limit['limit']);
        $windowSeconds = max(1, $limit['window_seconds']);
        $mode = '' !== $limit['mode'] ? $limit['mode'] : $this->defaultMode;
        $now = time();
        $bucketKey = $request->operation.'|'.($request->identity ?? 'anonymous');
        $bucket = $this->buckets[$bucketKey] ?? ['window_start' => $now, 'used' => 0];

        if (($now - $bucket['window_start']) >= $windowSeconds) {
            $bucket = ['window_start' => $now, 'used' => 0];
        }

        $remaining = $max - $bucket['used'];
        $retryAfter = max(1, $windowSeconds - ($now - $bucket['window_start']));

        if ($request->cost > $remaining) {
            $metadata = [
                'limiter' => 'in_memory',
                'operation' => $request->operation,
                'identity' => $request->identity,
                'cost' => $request->cost,
                'limit' => $max,
                'used' => $bucket['used'],
                'remaining' => max(0, $remaining),
                'window_seconds' => $windowSeconds,
            ];

            if ('defer' === $mode) {
                return SearchOperationLimitDecision::defer('search_operation_rate_limited', $retryAfter, $metadata);
            }

            return SearchOperationLimitDecision::reject('search_operation_rate_limited', $retryAfter, $metadata);
        }

        $bucket['used'] += $request->cost;
        $this->buckets[$bucketKey] = $bucket;

        return SearchOperationLimitDecision::allow([
            'limiter' => 'in_memory',
            'operation' => $request->operation,
            'identity' => $request->identity,
            'cost' => $request->cost,
            'limit' => $max,
            'used' => $bucket['used'],
            'remaining' => max(0, $max - $bucket['used']),
            'window_seconds' => $windowSeconds,
        ]);
    }
}
