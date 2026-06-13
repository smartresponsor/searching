<?php

declare(strict_types=1);

namespace App\Searching\Service\Provider;

use App\Searching\Service\Registry\SearchProviderRegistry;
use App\Searching\Value\Provider\SearchProviderStatus;

final readonly class SearchProviderStatusCollector
{
    public function __construct(
        private SearchProviderRegistry $providerRegistry,
    ) {
    }

    /**
     * @return array<string, SearchProviderStatus>
     */
    public function collect(): array
    {
        $statuses = [];

        foreach ($this->providerRegistry->all() as $nameEntity => $provider) {
            $statuses[$nameEntity] = $provider->getStatus();
        }

        return $statuses;
    }
}
