<?php

declare(strict_types=1);

namespace App\Searching\Tests\DependencyInjection;

use App\Searching\DependencyInjection\Compiler\SearchableDocumentProviderPass;
use App\Searching\DependencyInjection\Compiler\SearchProviderPass;
use App\Searching\DependencyInjection\Compiler\SearchResultHydratorPass;
use App\Searching\DependencyInjection\Configuration;
use App\Searching\DependencyInjection\SearchingExtension;
use App\Searching\Provider\Backend\SearchElasticsearchProvider;
use App\Searching\Provider\Backend\SearchOpenSearchProvider;
use App\Searching\SearchingBundle;
use App\Searching\Service\Query\SearchResultHydrator;
use App\Searching\Service\Registry\SearchableResourceRegistry;
use App\Searching\Service\Registry\SearchProviderRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class SearchingDependencyInjectionCoverageTest extends TestCase
{
    public function testConfigurationAppliesDefaultsAndConfiguredValues(): void
    {
        $processor = new Processor();
        $defaults = $processor->processConfiguration(new Configuration(), [[]]);
        self::assertTrue($defaults['enabled']);
        self::assertSame('null', $defaults['default_provider']);
        self::assertIsArray($defaults['query']);
        self::assertIsArray($defaults['indexing']);
        self::assertIsArray($defaults['indexing']['reindex']);
        self::assertSame(20, $defaults['query']['default_limit']);
        self::assertSame('sync', $defaults['indexing']['reindex']['dispatch_mode']);

        $configured = $processor->processConfiguration(new Configuration(), [[
            'default_provider' => 'elasticsearch',
            'providers' => [
                'elasticsearch' => ['enabled' => true, 'dsn' => 'http://localhost:9200', 'index_prefix' => 'search'],
            ],
            'indexing' => ['batch_size' => 25, 'reindex' => ['dispatch_mode' => 'messenger', 'messenger_max_attempts' => 5, 'duplicate_guard' => false]],
            'query' => ['default_limit' => 15, 'max_limit' => 75, 'highlights' => false],
            'logging' => ['enabled' => true, 'driver' => 'doctrine', 'recent_limit' => 30],
            'hydration' => ['enabled' => false],
            'security' => ['permission_filtering' => false],
            'flow_control' => ['enabled' => true, 'default_mode' => 'defer', 'operations' => [
                'search.query' => ['limit' => 10, 'window_seconds' => 30, 'mode' => 'defer'],
            ]],
        ]]);
        self::assertSame('elasticsearch', $configured['default_provider']);
        self::assertIsArray($configured['indexing']);
        self::assertIsArray($configured['indexing']['reindex']);
        self::assertIsArray($configured['flow_control']);
        self::assertIsArray($configured['flow_control']['operations']);
        self::assertIsArray($configured['flow_control']['operations']['search.query']);
        self::assertSame('messenger', $configured['indexing']['reindex']['dispatch_mode']);
        self::assertSame(10, $configured['flow_control']['operations']['search.query']['limit']);
    }

    public function testExtensionRegistersDefaultAndMessengerContours(): void
    {
        $default = new ContainerBuilder();
        (new SearchingExtension())->load([], $default);
        self::assertSame('null', $default->getParameter('searching.default_provider'));
        self::assertTrue($default->hasDefinition(SearchElasticsearchProvider::class));
        self::assertTrue($default->hasDefinition(SearchOpenSearchProvider::class));
        self::assertSame('sync', $default->getParameter('searching.indexing.reindex.dispatch_mode'));

        $messenger = new ContainerBuilder();
        (new SearchingExtension())->load([[
            'default_provider' => 'opensearch',
            'providers' => [
                'opensearch' => ['enabled' => true, 'dsn' => 'http://localhost:9200', 'index_prefix' => 'idx'],
            ],
            'indexing' => ['track_document_hash' => false, 'reindex' => ['dispatch_mode' => 'messenger', 'duplicate_guard' => false]],
            'logging' => ['enabled' => true, 'driver' => 'doctrine'],
            'hydration' => ['enabled' => false],
            'security' => ['permission_filtering' => false],
            'flow_control' => ['enabled' => true],
        ]], $messenger);
        self::assertSame('opensearch', $messenger->getParameter('searching.default_provider'));
        self::assertSame('messenger', $messenger->getParameter('searching.indexing.reindex.dispatch_mode'));
        self::assertFalse($messenger->getParameter('searching.indexing.reindex.duplicate_guard'));
    }

    public function testCompilerPassesWireTaggedServicesAndBundleRegistersPasses(): void
    {
        $providers = new ContainerBuilder();
        $providers->register(SearchProviderRegistry::class);
        $providers->register('provider.one')->addTag('searching.provider', ['nameEntity' => 'one']);
        $providers->register('provider.two')->addTag('searching.provider');
        (new SearchProviderPass())->process($providers);
        self::assertCount(2, $providers->getDefinition(SearchProviderRegistry::class)->getMethodCalls());
        (new SearchProviderPass())->process(new ContainerBuilder());

        $resources = new ContainerBuilder();
        $resources->register(SearchableResourceRegistry::class);
        $resources->register('resource.one')->addTag('searching.searchable_document_provider');
        (new SearchableDocumentProviderPass())->process($resources);
        self::assertCount(1, $resources->getDefinition(SearchableResourceRegistry::class)->getMethodCalls());
        (new SearchableDocumentProviderPass())->process(new ContainerBuilder());

        $hydrators = new ContainerBuilder();
        $hydrators->register(SearchResultHydrator::class);
        $hydrators->register('hydrator.one')->addTag('searching.search_result_hydrator');
        (new SearchResultHydratorPass())->process($hydrators);
        self::assertCount(1, $hydrators->getDefinition(SearchResultHydrator::class)->getMethodCalls());
        (new SearchResultHydratorPass())->process(new ContainerBuilder());

        $bundleContainer = new ContainerBuilder();
        (new SearchingBundle())->build($bundleContainer);
        $passes = $bundleContainer->getCompilerPassConfig()->getBeforeOptimizationPasses();
        self::assertTrue((bool) array_filter($passes, static fn (object $pass): bool => $pass instanceof SearchableDocumentProviderPass));
        self::assertTrue((bool) array_filter($passes, static fn (object $pass): bool => $pass instanceof SearchProviderPass));
        self::assertTrue((bool) array_filter($passes, static fn (object $pass): bool => $pass instanceof SearchResultHydratorPass));
    }
}
