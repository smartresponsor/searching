<?php

declare(strict_types=1);

namespace App\Searching\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Defines the configuration responsibility within the Searching component runtime and its typed boundaries.
 */
final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('searching');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->booleanNode('enabled')->defaultTrue()->end()
                ->scalarNode('default_provider')->defaultValue('null')->end()
                ->arrayNode('providers')
                    ->useAttributeAsKey('nameEntity')
                    ->arrayPrototype()
                        ->children()
                            ->booleanNode('enabled')->defaultFalse()->end()
                            ->scalarNode('dsn')->defaultNull()->end()
                            ->scalarNode('index_prefix')->defaultValue('sr')->end()
                            ->arrayNode('options')
                                ->normalizeKeys(false)
                                ->variablePrototype()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('indexing')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('batch_size')->defaultValue(100)->min(1)->end()
                        ->booleanNode('track_document_hash')->defaultTrue()->end()
                        ->arrayNode('reindex')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->enumNode('dispatch_mode')->values(['sync', 'messenger'])->defaultValue('sync')->end()
                                ->integerNode('messenger_max_attempts')->defaultValue(3)->min(1)->end()
                                ->booleanNode('duplicate_guard')->defaultTrue()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('query')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('default_limit')->defaultValue(20)->min(1)->end()
                        ->integerNode('max_limit')->defaultValue(100)->min(1)->end()
                        ->booleanNode('highlights')->defaultTrue()->end()
                        ->booleanNode('facets')->defaultTrue()->end()
                    ->end()
                ->end()
                ->arrayNode('logging')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultFalse()->end()
                        ->enumNode('driver')->values(['null', 'doctrine'])->defaultValue('null')->end()
                        ->booleanNode('flush_immediately')->defaultTrue()->end()
                        ->integerNode('recent_limit')->defaultValue(50)->min(1)->end()
                    ->end()
                ->end()
                ->arrayNode('hydration')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->end()
                    ->end()
                ->end()
                ->arrayNode('security')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('permission_filtering')->defaultTrue()->end()
                    ->end()
                ->end()
                ->arrayNode('flow_control')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultFalse()->end()
                        ->enumNode('default_mode')->values(['reject', 'defer'])->defaultValue('reject')->end()
                        ->arrayNode('operations')
                            ->useAttributeAsKey('nameEntity')
                            ->arrayPrototype()
                                ->children()
                                    ->integerNode('limit')->defaultValue(60)->min(1)->end()
                                    ->integerNode('window_seconds')->defaultValue(60)->min(1)->end()
                                    ->enumNode('mode')->values(['reject', 'defer'])->defaultValue('reject')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
