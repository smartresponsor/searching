<?php

declare(strict_types=1);

namespace App\Searching\DependencyInjection\Compiler;

use App\Searching\Service\Registry\SearchableResourceRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Defines the searchable document provider pass responsibility within the Searching component runtime and its typed boundaries.
 */
final class SearchableDocumentProviderPass implements CompilerPassInterface
{
    /**
     * Processes the process through the Searching component runtime workflow.
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(SearchableResourceRegistry::class)) {
            return;
        }

        $registry = $container->findDefinition(SearchableResourceRegistry::class);

        foreach ($container->findTaggedServiceIds('searching.searchable_document_provider') as $serviceId => $tags) {
            $registry->addMethodCall('add', [new Reference($serviceId)]);
        }
    }
}
