<?php

declare(strict_types=1);

namespace App\Searching\Contract\Registry;

use App\Searching\Contract\Producer\SearchableDocumentProviderInterface;
use App\Searching\ValueObject\Registry\SearchableResourceDefinition;

/**
 * Defines the searchable resource registry interface responsibility within the Searching component runtime and its typed boundaries.
 */
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

    /**
     * Executes the add responsibility defined by the Searching component contract.
     */
    public function add(SearchableDocumentProviderInterface $provider): void;
}
