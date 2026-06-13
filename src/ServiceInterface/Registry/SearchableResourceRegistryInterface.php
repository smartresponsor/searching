<?php

declare(strict_types=1);

namespace App\Searching\ServiceInterface\Registry;

use App\Searching\ServiceInterface\Producer\SearchableDocumentProviderInterface;
use App\Searching\Value\Registry\SearchableResourceDefinition;

interface SearchableResourceRegistryInterface
{
    /**
     * @return list<SearchableDocumentProviderInterface>
     */
    public function all(): array;

    /**
     * @return list<SearchableResourceDefinition>
     */
    public function definitions(): array;

    /**
     * @return list<SearchableDocumentProviderInterface>
     */
    public function matching(?string $component = null, ?string $resourceType = null): array;

    public function add(SearchableDocumentProviderInterface $provider): void;
}
