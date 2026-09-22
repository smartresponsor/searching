<?php

declare(strict_types=1);

namespace App\Searching\Contract\Registry;

use App\Searching\Contract\Producer\SearchableDocumentProviderInterface;
use App\Searching\ValueObject\Registry\SearchableResourceDefinition;

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
