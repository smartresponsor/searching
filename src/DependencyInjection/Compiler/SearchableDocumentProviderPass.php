<?php

declare(strict_types=1);

namespace App\Searching\DependencyInjection\Compiler;

use App\Searching\Service\Registry\SearchableResourceRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class SearchableDocumentProviderPass implements CompilerPassInterface
{
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
