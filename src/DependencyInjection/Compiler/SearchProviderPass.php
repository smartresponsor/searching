<?php

declare(strict_types=1);

namespace App\Searching\DependencyInjection\Compiler;

use App\Searching\Service\Registry\SearchProviderRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class SearchProviderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(SearchProviderRegistry::class)) {
            return;
        }

        $registry = $container->findDefinition(SearchProviderRegistry::class);

        foreach ($container->findTaggedServiceIds('searching.provider') as $serviceId => $tags) {
            foreach ($tags as $tag) {
                if (!is_array($tag)) {
                    continue;
                }

                $nameEntity = isset($tag['nameEntity']) && is_string($tag['nameEntity']) ? $tag['nameEntity'] : $serviceId;
                $registry->addMethodCall('add', [$nameEntity, new Reference($serviceId)]);
            }
        }
    }
}
