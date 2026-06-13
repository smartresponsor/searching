<?php

declare(strict_types=1);

namespace App\Searching\Service\Serialization;

use App\Searching\Value\Registry\SearchableResourceDefinition;

final class SearchRegistrySerializer
{
    /**
     * @param list<SearchableResourceDefinition> $definitions
     *
     * @return array<string, mixed>
     */
    public function serializeDefinitions(array $definitions): array
    {
        return [
            'total' => count($definitions),
            'resources' => array_map($this->serializeDefinition(...), $definitions),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function serializeDefinition(SearchableResourceDefinition $definition): array
    {
        return [
            'key' => $definition->key(),
            'component' => $definition->component,
            'resourceType' => $definition->resourceType,
            'providerClass' => $definition->providerClass,
        ];
    }
}
