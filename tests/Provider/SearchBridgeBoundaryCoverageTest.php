<?php

declare(strict_types=1);

namespace App\Searching\Tests\Provider;

use App\Searching\Contract\Provider\SearchProviderInterface;
use App\Searching\Contract\Query\SearchQueryExecutorInterface;
use App\Searching\Contract\Query\SearchResponseProviderInterface;
use App\Searching\Contract\Query\SearchSuggestionProviderInterface;
use App\Searching\Contract\Query\SearchSuggestionResponseProviderInterface;
use App\Searching\Contract\Registry\SearchableResourceRegistryInterface;
use App\Searching\Controller\Api\SearchBridgeApiController;
use App\Searching\Provider\Bridge\SearchInterfacingBridgeProvider;
use App\Searching\Provider\SearchResponseProvider;
use App\Searching\Resolver\Query\SearchFacetResolver;
use App\Searching\Service\Bridge\SearchBridgeConfigSerializer;
use App\Searching\Service\SearchResponseMapper;
use App\Searching\Service\SearchResponseSerializer;
use App\Searching\ValueObject\Observability\SearchExecutionContext;
use App\Searching\ValueObject\Provider\SearchCapability;
use App\Searching\ValueObject\Provider\SearchProviderStatus;
use App\Searching\ValueObject\Query\SearchQuery;
use App\Searching\ValueObject\Query\SearchQueryRequest;
use App\Searching\ValueObject\Query\SearchSuggestionQuery;
use App\Searching\ValueObject\Query\SearchSuggestionRequest;
use App\Searching\ValueObject\Registry\SearchableResourceDefinition;
use App\Searching\ValueObject\Result\SearchResponse;
use App\Searching\ValueObject\Result\SearchResult;
use App\Searching\ValueObject\Result\SearchSuggestion;
use App\Searching\ValueObject\Result\SearchSuggestionResponse;
use PHPUnit\Framework\TestCase;

final class SearchBridgeBoundaryCoverageTest extends TestCase
{
    public function testResponseProviderMapsSearchSuggestionsAndCapability(): void
    {
        $context = new SearchExecutionContext('corr-provider', actorId: 'user-1');
        $executor = $this->createMock(SearchQueryExecutorInterface::class);
        $executor->expects(self::once())->method('execute')->with(self::callback(
            static fn (SearchQuery $query): bool => 'needle' === $query->query && $context === $query->executionContext,
        ))->willReturn(new SearchResult('needle', 0, 1, 20, []));

        $suggestions = $this->createMock(SearchSuggestionProviderInterface::class);
        $suggestions->expects(self::once())->method('suggestByQuery')->with(self::callback(
            static fn (SearchSuggestionQuery $query): bool => 'need' === $query->query && 5 === $query->limit,
        ))->willReturn([new SearchSuggestion('Needle', 0.9, 'ordering', 'order', '42')]);

        $backend = $this->createMock(SearchProviderInterface::class);
        $backend->method('getStatus')->willReturn(new SearchProviderStatus('elastic', true, 'available'));

        $registry = $this->createMock(SearchableResourceRegistryInterface::class);
        $registry->method('definitions')->willReturn([
            new SearchableResourceDefinition('ordering', 'order', 'ProviderA'),
            new SearchableResourceDefinition('ordering', 'invoice', 'ProviderB'),
            new SearchableResourceDefinition('catalog', 'product', 'ProviderC'),
        ]);

        $provider = new SearchResponseProvider($executor, $suggestions, $backend, $registry, new SearchResponseMapper());
        $response = $provider->search(new SearchQueryRequest('needle', executionContext: $context));
        $mappedSuggestions = $provider->suggest(new SearchSuggestionRequest('need', limit: 5));
        $capability = $provider->getCapability();

        self::assertSame('needle', $response->query);
        self::assertSame('Needle', $mappedSuggestions[0]->text);
        self::assertTrue($capability->enabled);
        self::assertSame(['ordering', 'catalog'], $capability->supportedComponents);
        self::assertSame(['order', 'invoice', 'product'], $capability->supportedResourceTypes);
        self::assertSame('available', $capability->metadata['provider_status']);
    }

    public function testBridgeProviderBuildsConfigAndDelegatesRuntimeOperations(): void
    {
        $responseProvider = $this->createMock(SearchResponseProviderInterface::class);
        $responseProvider->method('getCapability')->willReturn(new SearchCapability(
            false,
            'elastic',
            ['query', 'facets', 'highlights'],
            ['ordering'],
            ['order'],
            ['provider_status' => 'unavailable'],
        ));
        $responseProvider->method('search')->willReturn(new SearchResponse('needle', 0, 1, 20, []));

        $suggestionProvider = $this->createMock(SearchSuggestionResponseProviderInterface::class);
        $suggestionProvider->method('suggest')->willReturn([
            new SearchSuggestionResponse('Needle', 1.0, 'ordering', 'order', '42'),
        ]);

        $bridge = new SearchInterfacingBridgeProvider($responseProvider, $suggestionProvider);
        $config = $bridge->getBridgeConfig();

        self::assertFalse($config->autocomplete->enabled);
        self::assertTrue($config->degradedState->degraded);
        self::assertSame('elastic', $config->degradedState->providerName);
        self::assertTrue($config->resultPage->metadata['facets_enabled']);
        self::assertCount(5, $config->routeHints);
        self::assertSame('needle', $bridge->search(new SearchQueryRequest('needle'))->query);
        self::assertSame('Needle', $bridge->suggest(new SearchSuggestionRequest('need'))[0]->text);
    }

    public function testBridgeApiSerializesBridgeConfig(): void
    {
        $responseProvider = $this->createMock(SearchResponseProviderInterface::class);
        $responseProvider->method('getCapability')->willReturn(new SearchCapability(true, 'null'));
        $suggestionProvider = $this->createMock(SearchSuggestionResponseProviderInterface::class);
        $bridge = new SearchInterfacingBridgeProvider($responseProvider, $suggestionProvider);
        $controller = new SearchBridgeApiController(
            $bridge,
            new SearchBridgeConfigSerializer(new SearchResponseSerializer()),
        );

        $response = $controller->interfacing();

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('"bridge_name":"interfacing_search_bridge"', (string) $response->getContent());
        self::assertStringContainsString('"providerName":"null"', (string) $response->getContent());
    }

    public function testQueryRequestsPreserveRuntimeFieldsWhenConverted(): void
    {
        $context = new SearchExecutionContext('corr-request', actorId: 'user-2');
        $query = (new SearchQueryRequest(
            query: 'needle',
            components: ['ordering'],
            resourceTypes: ['order'],
            filters: ['status' => 'open'],
            sort: ['createdAt' => 'desc'],
            page: 2,
            limit: 7,
            locale: 'en',
            vendorId: 'vendor-1',
            userId: 'user-2',
            userPermissions: ['order.view'],
            includeHighlights: false,
            includeFacets: false,
            executionContext: $context,
        ))->toInternalQuery();

        self::assertSame(['createdAt' => 'desc'], $query->sort);
        self::assertSame(['order.view'], $query->userPermissions);
        self::assertFalse($query->includeHighlights);
        self::assertFalse($query->includeFacets);
        self::assertSame($context, $query->executionContext);

        $suggestion = (new SearchSuggestionRequest(
            query: 'need',
            components: ['catalog'],
            resourceTypes: ['product'],
            filters: ['active' => true],
            limit: 4,
            locale: 'en',
            vendorId: 'vendor-1',
            userId: 'user-2',
            includeSynonyms: false,
            includeFuzzy: false,
            executionContext: $context,
        ))->toInternalQuery();

        self::assertSame(['active' => true], $suggestion->filters);
        self::assertFalse($suggestion->includeSynonyms);
        self::assertFalse($suggestion->includeFuzzy);
        self::assertSame($context, $suggestion->executionContext);
    }

    public function testFacetResolverReturnsProviderNeutralFacetPayload(): void
    {
        $facets = ['status' => ['open' => 3, 'closed' => 1]];

        self::assertSame($facets, (new SearchFacetResolver())->resolve($facets));
    }
}
