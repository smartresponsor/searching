<?php

declare(strict_types=1);

namespace App\Searching\Service\Registry;

use App\Searching\Contract\Producer\SearchableDocumentProviderInterface;
use App\Searching\Contract\Registry\SearchableResourceRegistryInterface;
use App\Searching\ValueObject\Registry\SearchableResourceDefinition;

final class SearchableResourceRegistry implements SearchableResourceRegistryInterface
{
    /** @var array<string, SearchableDocumentProviderInterface> */
    private array $providers = [];

    public function all(): array
    {
        return array_values($this->providers);
    }

    public function definitions(): array
    {
        return array_values(array_map(
            static fn (SearchableDocumentProviderInterface $provider): SearchableResourceDefinition => new SearchableResourceDefinition(
                $provider->getSearchableComponentName(),
                $provider->getSearchableResourceName(),
                $provider::class,
            ),
            $this->providers,
        ));
    }

    public function matching(?string $component = null, ?string $resourceType = null): array
    {
        return array_values(array_filter(
            $this->providers,
            static fn (SearchableDocumentProviderInterface $provider): bool => (null === $component || $provider->getSearchableComponentName() === $component)
                && (null === $resourceType || $provider->getSearchableResourceName() === $resourceType),
        ));
    }

    public function add(SearchableDocumentProviderInterface $provider): void
    {
        $key = $provider->getSearchableComponentName().':'.$provider->getSearchableResourceName();
        $this->providers[$key] = $provider;
    }
}
