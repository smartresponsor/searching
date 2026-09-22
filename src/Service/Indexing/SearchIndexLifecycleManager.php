<?php

declare(strict_types=1);

namespace App\Searching\Service\Indexing;

use App\Searching\Builder\Provider\SearchIndexNameBuilder;
use App\Searching\Contract\Indexing\SearchIndexLifecycleManagerInterface;
use App\Searching\Contract\Indexing\SearchIndexLifecycleRegistrySynchronizerInterface;
use App\Searching\Contract\Provider\SearchIndexLifecycleProviderInterface;
use App\Searching\Service\Registry\SearchProviderRegistry;
use App\Searching\ValueObject\Provider\SearchIndexLifecycleResult;

final readonly class SearchIndexLifecycleManager implements SearchIndexLifecycleManagerInterface
{
    public function __construct(
        private SearchProviderRegistry $providerRegistry,
        private SearchIndexNameBuilder $indexNameBuilder,
        private string $defaultIndexPrefix = 'sr',
        private ?SearchIndexLifecycleRegistrySynchronizerInterface $registrySynchronizer = null,
    ) {
    }

    public function ensure(string $providerName, string $component, string $resourceType): SearchIndexLifecycleResult
    {
        $provider = $this->providerRegistry->get($providerName);
        if (!$provider instanceof SearchIndexLifecycleProviderInterface) {
            $result = SearchIndexLifecycleResult::unavailable(
                providerName: $providerName,
                indexName: $this->fallbackIndexName($component, $resourceType),
                operation: 'ensure',
                reason: 'Provider does not expose index lifecycle operations.',
            );
            $this->syncRegistry($component, $resourceType, $result);

            return $result;
        }

        $result = $provider->ensureIndex($component, $resourceType);
        $this->syncRegistry($component, $resourceType, $result);

        return $result;
    }

    public function delete(string $providerName, string $component, string $resourceType): SearchIndexLifecycleResult
    {
        $provider = $this->providerRegistry->get($providerName);
        if (!$provider instanceof SearchIndexLifecycleProviderInterface) {
            $result = SearchIndexLifecycleResult::unavailable(
                providerName: $providerName,
                indexName: $this->fallbackIndexName($component, $resourceType),
                operation: 'delete',
                reason: 'Provider does not expose index lifecycle operations.',
            );
            $this->syncRegistry($component, $resourceType, $result);

            return $result;
        }

        $result = $provider->deleteIndex($component, $resourceType);
        $this->syncRegistry($component, $resourceType, $result);

        return $result;
    }

    public function ensureForAllProviders(string $component, string $resourceType): array
    {
        $results = [];
        foreach (array_keys($this->providerRegistry->all()) as $providerName) {
            $results[$providerName] = $this->ensure($providerName, $component, $resourceType);
        }

        return $results;
    }

    private function fallbackIndexName(string $component, string $resourceType): string
    {
        return $this->indexNameBuilder->buildForParts($this->defaultIndexPrefix, $component, $resourceType);
    }

    private function syncRegistry(string $component, string $resourceType, SearchIndexLifecycleResult $result): void
    {
        $this->registrySynchronizer?->sync($component, $resourceType, $result);
    }
}
