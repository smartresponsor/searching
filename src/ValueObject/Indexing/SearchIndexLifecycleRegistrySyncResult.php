<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Indexing;

/**
 * Defines the search index lifecycle registry sync result responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchIndexLifecycleRegistrySyncResult
{
    public function __construct(
        public bool $synced,
        public string $status,
        public ?string $reason = null,
    ) {
    }

    /**
     * Executes the synced responsibility defined by the Searching component contract.
     */
    public static function synced(string $status = 'synced'): self
    {
        return new self(true, $status);
    }

    /**
     * Executes the skipped responsibility defined by the Searching component contract.
     */
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
