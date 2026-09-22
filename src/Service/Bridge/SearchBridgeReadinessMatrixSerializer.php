<?php

declare(strict_types=1);

namespace App\Searching\Service\Bridge;

use App\Searching\ValueObject\Bridge\SearchBridgeReadinessItem;
use App\Searching\ValueObject\Bridge\SearchBridgeReadinessMatrix;

/**
 * Defines the search bridge readiness matrix serializer responsibility within the Searching component runtime and its typed boundaries.
 */
final class SearchBridgeReadinessMatrixSerializer
{
    /**
     * @return array{version: string, summary: string, items: list<array{area: string, status: string, owner: string, consumer: string, contract: string, note: string}>}
     */
    public function serialize(SearchBridgeReadinessMatrix $matrix): array
    {
        return [
            'version' => $matrix->version,
            'summary' => $matrix->summary,
            'items' => array_map(
                static fn (SearchBridgeReadinessItem $item): array => [
                    'area' => $item->area,
                    'status' => $item->status,
                    'owner' => $item->owner,
                    'consumer' => $item->consumer,
                    'contract' => $item->contract,
                    'note' => $item->note,
                ],
                $matrix->items
            ),
        ];
    }
}
