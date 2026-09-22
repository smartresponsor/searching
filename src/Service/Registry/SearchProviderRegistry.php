<?php

declare(strict_types=1);

namespace App\Searching\Service\Registry;

use App\Searching\Contract\Provider\SearchProviderInterface;

/**
 * Defines the search provider registry responsibility within the Searching component runtime and its typed boundaries.
 */
final class SearchProviderRegistry
{
    /** @var array<string, SearchProviderInterface> */
    private array $providers = [];

    public function add(string $nameEntity, SearchProviderInterface $provider): void
    {
        $this->providers[$nameEntity] = $provider;
    }

    public function get(string $nameEntity): ?SearchProviderInterface
    {
        return $this->providers[$nameEntity] ?? null;
    }

    /**
     * @return array<string, SearchProviderInterface>
     */
    public function all(): array
    {
        return $this->providers;
    }
}
