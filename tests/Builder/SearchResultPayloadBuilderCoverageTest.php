<?php

declare(strict_types=1);

namespace App\Searching\Tests\Builder;

use App\Searching\Builder\SearchResultPayloadBuilder;
use App\Searching\Contract\Observability\SearchExecutionContextResolverInterface;
use App\Searching\Contract\Query\SearchResponseProviderInterface;
use App\Searching\Exception\SearchOperationLimitedException;
use App\Searching\Factory\SearchResultPayloadFactory;
use App\Searching\Service\SearchResponseSerializer;
use App\Searching\ValueObject\Flow\SearchOperationLimitDecision;
use App\Searching\ValueObject\Flow\SearchOperationLimitRequest;
use App\Searching\ValueObject\Observability\SearchExecutionContext;
use App\Searching\ValueObject\Query\SearchQueryRequest;
use App\Searching\ValueObject\Result\SearchResponse;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class SearchResultPayloadBuilderCoverageTest extends TestCase
{
    public function testBuilderCreatesViewingPayloadFromNormalizedQuery(): void
    {
        $context = new SearchExecutionContext('corr-result', actorId: 'user-1');
        $resolver = $this->createMock(SearchExecutionContextResolverInterface::class);
        $resolver->expects(self::once())->method('resolve')->with(
            self::isInstanceOf(Request::class),
            'search.result.page',
            'user-1',
        )->willReturn($context);
        $provider = $this->createMock(SearchResponseProviderInterface::class);
        $provider->expects(self::once())->method('search')->with(self::callback(static function (SearchQueryRequest $request) use ($context): bool {
            return 'needle' === $request->query
                && ['ordering', 'catalog'] === $request->components
                && ['order'] === $request->resourceTypes
                && ['status' => 'open'] === $request->filters
                && 1 === $request->page
                && 100 === $request->limit
                && 'en' === $request->locale
                && 'vendor-1' === $request->vendorId
                && 'user-1' === $request->userId
                && false === $request->includeHighlights
                && true === $request->includeFacets
                && $context === $request->executionContext;
        }))->willReturn(new SearchResponse('needle', 3, 1, 100, []));

        $builder = new SearchResultPayloadBuilder($provider, $resolver, new SearchResultPayloadFactory(new SearchResponseSerializer()));
        $payload = $builder->build(new Request([
            'q' => ' needle ',
            'component' => 'ordering, catalog, ',
            'resourceType' => 'order',
            'filter' => ['status' => 'open', 'nested' => ['ignored']],
            'page' => '0',
            'limit' => '500',
            'locale' => ' en ',
            'vendorId' => ' vendor-1 ',
            'userId' => ' user-1 ',
            'highlights' => 'false',
            'facets' => 'true',
        ]));

        self::assertIsArray($payload['_view']);
        self::assertIsArray($payload['meta']);
        self::assertIsArray($payload['data']);
        self::assertSame('Searching', $payload['_view']['component']);
        self::assertSame(200, $payload['meta']['status_code']);
        self::assertSame('needle', $payload['data']['query']);
        $slots = $payload['data']['slots'];
        self::assertIsArray($slots);
        $rightPanel = $slots['right.panel'];
        self::assertIsArray($rightPanel);
        $stats = $rightPanel['stats'];
        self::assertIsArray($stats);
        self::assertIsArray($stats[0]);
        self::assertSame('3', $stats[0]['value']);
    }

    public function testBuilderMapsRejectedAndDeferredLimitsToViewingStatus(): void
    {
        $resolver = $this->createMock(SearchExecutionContextResolverInterface::class);
        $resolver->method('resolve')->willReturn(new SearchExecutionContext('corr-limit'));
        $provider = $this->createMock(SearchResponseProviderInterface::class);
        $provider->method('search')->willThrowException(new SearchOperationLimitedException(
            new SearchOperationLimitRequest('search.result.page'),
            SearchOperationLimitDecision::reject('busy', 4),
        ));
        $builder = new SearchResultPayloadBuilder($provider, $resolver, new SearchResultPayloadFactory(new SearchResponseSerializer()));

        $rejected = $builder->build(new Request());
        self::assertIsArray($rejected['meta']);
        self::assertIsArray($rejected['data']);
        self::assertIsArray($rejected['data']['error']);
        self::assertSame(429, $rejected['meta']['status_code']);
        self::assertSame('search_operation_limited', $rejected['data']['error']['code']);

        $deferredProvider = $this->createMock(SearchResponseProviderInterface::class);
        $deferredProvider->method('search')->willThrowException(new SearchOperationLimitedException(
            new SearchOperationLimitRequest('search.result.page'),
            SearchOperationLimitDecision::defer('later', 2),
        ));
        $deferredBuilder = new SearchResultPayloadBuilder($deferredProvider, $resolver, new SearchResultPayloadFactory(new SearchResponseSerializer()));
        $deferred = $deferredBuilder->build(new Request());
        self::assertIsArray($deferred['meta']);
        self::assertSame(202, $deferred['meta']['status_code']);
    }

    public function testPayloadFactoryFallsBackForNonScalarStats(): void
    {
        $factory = new SearchResultPayloadFactory(new SearchResponseSerializer());
        $payload = $factory->create('needle', null, ['code' => 'none']);

        self::assertSame('search/base.html.twig', $payload->templateName());
        $rightPanel = $payload->slots['right.panel'];
        self::assertIsArray($rightPanel);
        $stats = $rightPanel['stats'];
        self::assertIsArray($stats);
        self::assertIsArray($stats[0]);
        self::assertSame('0', $stats[0]['value']);
        $fallback = $payload->toFallbackData();
        self::assertIsArray($fallback['error']);
        self::assertSame('none', $fallback['error']['code']);
    }
}
