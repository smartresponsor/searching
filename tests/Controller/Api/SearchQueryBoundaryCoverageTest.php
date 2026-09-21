<?php

declare(strict_types=1);

namespace App\Searching\Tests\Controller\Api;

use App\Searching\Contract\Observability\SearchExecutionContextResolverInterface;
use App\Searching\Contract\Query\SearchQueryExecutorInterface;
use App\Searching\Contract\Query\SearchResponseProviderInterface;
use App\Searching\Contract\Query\SearchSuggestionProviderInterface;
use App\Searching\Contract\Query\SearchSuggestionResponseProviderInterface;
use App\Searching\Controller\Api\SearchApiController;
use App\Searching\Controller\Api\SearchResponseApiController;
use App\Searching\Controller\Api\SearchSuggestionApiController;
use App\Searching\Exception\SearchOperationLimitedException;
use App\Searching\Resolver\Observability\SearchExecutionContextResolver;
use App\Searching\Service\SearchResponseSerializer;
use App\Searching\Service\Serialization\SearchResultSerializer;
use App\Searching\Value\Flow\SearchOperationLimitDecision;
use App\Searching\Value\Flow\SearchOperationLimitRequest;
use App\Searching\Value\Observability\SearchExecutionContext;
use App\Searching\Value\Provider\SearchCapability;
use App\Searching\Value\Query\SearchQuery;
use App\Searching\Value\Query\SearchSuggestionQuery;
use App\Searching\Value\Result\SearchResponse;
use App\Searching\Value\Result\SearchResult;
use App\Searching\Value\Result\SearchSuggestion;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class SearchQueryBoundaryCoverageTest extends TestCase
{
    public function testSearchApiNormalizesRequestAndSerializesResult(): void
    {
        $context = new SearchExecutionContext('corr-1', actorId: 'user-1');
        $resolver = $this->createMock(SearchExecutionContextResolverInterface::class);
        $resolver->method('resolve')->willReturn($context);
        $executor = $this->createMock(SearchQueryExecutorInterface::class);
        $executor->expects(self::once())->method('execute')->with(self::callback(static function (SearchQuery $query) use ($context): bool {
            return 'needle' === $query->query
                && ['ordering', 'catalog'] === $query->components
                && ['order'] === $query->resourceTypes
                && ['status' => 'open'] === $query->filters
                && 1 === $query->page
                && 1 === $query->limit
                && 'en' === $query->locale
                && 'vendor-1' === $query->vendorId
                && 'user-1' === $query->userId
                && false === $query->includeHighlights
                && true === $query->includeFacets
                && $context === $query->executionContext;
        }))->willReturn(new SearchResult('needle', 0, 1, 1, []));

        $controller = new SearchApiController($executor, new SearchResultSerializer(), $resolver);
        $response = $controller(new Request([
            'q' => ' needle ',
            'component' => 'ordering, catalog',
            'resourceType' => 'order',
            'filter' => ['status' => 'open', 'nested' => ['ignored']],
            'page' => '0',
            'limit' => '0',
            'locale' => ' en ',
            'vendorId' => ' vendor-1 ',
            'userId' => ' user-1 ',
            'highlights' => 'false',
            'facets' => 'true',
        ]));

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('"query":"needle"', (string) $response->getContent());
    }

    public function testSearchApiMapsLimitedOperationToRetryResponse(): void
    {
        $resolver = $this->createMock(SearchExecutionContextResolverInterface::class);
        $resolver->method('resolve')->willReturn(new SearchExecutionContext('corr-2'));
        $executor = $this->createMock(SearchQueryExecutorInterface::class);
        $executor->method('execute')->willThrowException(new SearchOperationLimitedException(
            new SearchOperationLimitRequest('search.query'),
            SearchOperationLimitDecision::reject('busy', 7),
        ));

        $response = (new SearchApiController($executor, new SearchResultSerializer(), $resolver))(new Request());

        self::assertSame(429, $response->getStatusCode());
        self::assertSame('7', $response->headers->get('Retry-After'));
    }

    public function testResponseApiCoversSearchSuggestCapabilityAndDeferredLimit(): void
    {
        $context = new SearchExecutionContext('corr-3', actorId: 'user-3');
        $resolver = $this->createMock(SearchExecutionContextResolverInterface::class);
        $resolver->method('resolve')->willReturn($context);
        $responseProvider = $this->createMock(SearchResponseProviderInterface::class);
        $responseProvider->method('search')->willReturn(new SearchResponse('needle', 0, 1, 2, []));
        $responseProvider->method('getCapability')->willReturn(new SearchCapability(true, 'null', ['query'], ['ordering'], ['order']));
        $suggestionProvider = $this->createMock(SearchSuggestionResponseProviderInterface::class);
        $suggestionProvider->method('suggest')->willReturn([]);

        $controller = new SearchResponseApiController($responseProvider, $suggestionProvider, new SearchResponseSerializer(), $resolver);
        self::assertSame(200, $controller->search(new Request(['q' => ' needle ', 'component' => 'ordering', 'limit' => '2']))->getStatusCode());
        self::assertSame(200, $controller->suggest(new Request(['q' => ' need ', 'limit' => '99', 'synonyms' => 'false']))->getStatusCode());
        self::assertStringContainsString('"providerName":"null"', (string) $controller->capability()->getContent());

        $limited = $this->createMock(SearchResponseProviderInterface::class);
        $limited->method('search')->willThrowException(new SearchOperationLimitedException(
            new SearchOperationLimitRequest('search.response.query'),
            SearchOperationLimitDecision::defer('later', 3),
        ));
        $limitedController = new SearchResponseApiController($limited, $suggestionProvider, new SearchResponseSerializer(), $resolver);
        self::assertSame(202, $limitedController->search(new Request())->getStatusCode());
    }

    public function testSuggestionApiHandlesArrayAliasesAndExecutionContext(): void
    {
        $context = new SearchExecutionContext('corr-4', actorId: 'user-4');
        $resolver = $this->createMock(SearchExecutionContextResolverInterface::class);
        $resolver->method('resolve')->willReturn($context);
        $provider = $this->createMock(SearchSuggestionProviderInterface::class);
        $provider->expects(self::once())->method('suggestByQuery')->with(self::callback(static function (SearchSuggestionQuery $query) use ($context): bool {
            return ['ordering', 'catalog'] === $query->components
                && ['order'] === $query->resourceTypes
                && 50 === $query->limit
                && 'user-4' === $query->userId
                && 'vendor-4' === $query->vendorId
                && false === $query->includeSynonyms
                && $context === $query->executionContext;
        }))->willReturn([new SearchSuggestion('Needle', 1.0, 'ordering', 'order', '42')]);

        $response = (new SearchSuggestionApiController($provider, new SearchResultSerializer(), $resolver))(new Request([
            'q' => ' needle ',
            'components' => 'ordering,catalog',
            'resources' => 'order',
            'limit' => '99',
            'user_id' => ' user-4 ',
            'vendor_id' => ' vendor-4 ',
            'synonyms' => 'false',
        ]));

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('"text":"Needle"', (string) $response->getContent());
    }

    public function testExecutionContextResolverUsesStackHeadersAndFallback(): void
    {
        $request = new Request(server: ['REQUEST_METHOD' => 'POST']);
        $request->headers->set('X-Request-Id', 'req-1');
        $request->headers->set('X-Source-Component', 'bridging');
        $request->headers->set('X-Actor-Id', 'user-5');
        $request->attributes->set('_route', 'search_route');
        $stack = new RequestStack();
        $stack->push($request);
        $resolver = new SearchExecutionContextResolver($stack);

        $context = $resolver->resolve(sourceOperation: 'search.query');
        self::assertSame('req-1', $context->correlationId);
        self::assertSame('req-1', $context->requestId);
        self::assertSame('bridging', $context->sourceComponent);
        self::assertSame('user-5', $context->actorId);
        self::assertSame('POST', $context->metadata['http_method']);
        self::assertSame('search_route', $context->metadata['route']);

        $fallback = (new SearchExecutionContextResolver())->resolve(sourceOperation: 'search.health', actorId: 'ops');
        self::assertSame('search.health', $fallback->sourceOperation);
        self::assertSame('ops', $fallback->actorId);
    }
}
