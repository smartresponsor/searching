<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Indexing;

final readonly class SearchIndexLifecycleRegistrySyncResult
{
    public function __construct(
        public bool $synced,
        public string $status,
        public ?string $reason = null,
    ) {
    }

    public static function synced(string $status = 'synced'): self
    {
        return new self(true, $status);
    }

    public static function skipped(string $reason): self
    {
        return new self(false, 'skipped', $reason);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'synced' => $this->synced,
            'status' => $this->status,
            'reason' => $this->reason,
        ];
    }
}
