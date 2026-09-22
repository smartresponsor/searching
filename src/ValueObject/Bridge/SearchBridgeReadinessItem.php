<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Bridge;

final readonly class SearchBridgeReadinessItem
{
    public function __construct(
        public string $area,
        public string $status,
        public string $owner,
        public string $consumer,
        public string $contract,
        public string $note,
    ) {
    }
}
