<?php

declare(strict_types=1);

namespace App\Searching\DependencyInjection\Compiler;

use App\Searching\Service\Query\SearchResultHydrator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Defines the search result hydrator pass responsibility within the Searching component runtime and its typed boundaries.
 */
final class SearchResultHydratorPass implements CompilerPassInterface
{
    /**
     * Processes the process through the Searching component runtime workflow.
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(SearchResultHydrator::class)) {
            return;
        }

        $hydrator = $container->findDefinition(SearchResultHydrator::class);

        foreach ($container->findTaggedServiceIds('searching.search_result_hydrator') as $serviceId => $tags) {
            $hydrator->addMethodCall('add', [new Reference($serviceId)]);
        }
    }
}
