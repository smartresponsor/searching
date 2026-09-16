<?php

declare(strict_types=1);

namespace App\Searching\Tests\Controller\Api;

use App\Searching\Contract\Indexing\SearchReindexCoordinatorInterface;
use App\Searching\Contract\Indexing\SearchReindexDispatcherInterface;
use App\Searching\Contract\Observability\SearchExecutionContextResolverInterface;
use App\Searching\Controller\Api\SearchReindexApiController;
use App\Searching\Service\Serialization\SearchReindexDispatchResultSerializer;
use App\Searching\Service\Serialization\SearchReindexResultSerializer;
use App\Searching\Value\Indexing\SearchReindexDispatchResult;
use App\Searching\Value\Indexing\SearchReindexResult;
use App\Searching\Value\Observability\SearchExecutionContext;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class SearchReindexApiControllerCoverageTest extends TestCase
{
    public function testSyncAndQueuedContracts(): void
    {
        $context = new SearchExecutionContext('corr-1', actorId: 'operator');
        $resolver = $this->createMock(SearchExecutionContextResolverInterface::class);
        $resolver->method('resolve')->willReturn($context);
        $coordinator = $this->createMock(SearchReindexCoordinatorInterface::class);
        $coordinator->method('reindex')->willReturn(new SearchReindexResult('job-sync', 1, 2, executionContext: $context));
        $dispatcher = $this->createMock(SearchReindexDispatcherInterface::class);
        $dispatcher->method('dispatch')->willReturn(new SearchReindexDispatchResult('job-queued', 'async', true));
        $controller = $this->controller($coordinator, $dispatcher, $resolver);

        $sync = $controller(new Request(content: '{"component":" cataloging ","resource":" product ","requested_by":" operator ","changed_since":"2026-09-15T12:00:00+00:00"}'));
        /** @var array{jobId:string,metadata:array{execution_context:array{correlation_id:string}}} $syncPayload */
        $syncPayload = json_decode((string) $sync->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame(200, $sync->getStatusCode());
        self::assertSame('job-sync', $syncPayload['jobId']);
        self::assertSame('corr-1', $syncPayload['metadata']['execution_context']['correlation_id']);

        $queued = $controller(new Request(content: '{"component":"cataloging","resourceType":"product","async":true}'));
        self::assertSame(202, $queued->getStatusCode());
    }

    public function testLimitedDispatchMapsStatusAndRetryAfter(): void
    {
        foreach ([[false, 429], [true, 202]] as [$deferred, $expectedStatus]) {
            $resolver = $this->createMock(SearchExecutionContextResolverInterface::class);
            $resolver->method('resolve')->willReturn(new SearchExecutionContext('corr-limit'));
            $dispatcher = $this->createMock(SearchReindexDispatcherInterface::class);
            $dispatcher->method('dispatch')->willReturn(new SearchReindexDispatchResult(
                'job-limit',
                'async',
                false,
                metadata: ['operation_limit' => [
                    'allowed' => false,
                    'deferred' => $deferred,
                    'retry_after_seconds' => 45,
                ]],
            ));

            $response = $this->controller(
                $this->createMock(SearchReindexCoordinatorInterface::class),
                $dispatcher,
                $resolver,
            )(new Request(content: '{"queued":true}'));

            self::assertSame($expectedStatus, $response->getStatusCode());
            self::assertSame('45', $response->headers->get('Retry-After'));
        }
    }

    public function testInvalidJsonFallsBackToEmptyPayload(): void
    {
        $resolver = $this->createMock(SearchExecutionContextResolverInterface::class);
        $resolver->method('resolve')->willReturn(new SearchExecutionContext('corr-empty'));
        $coordinator = $this->createMock(SearchReindexCoordinatorInterface::class);
        $coordinator->expects(self::once())
            ->method('reindex')
            ->with(null, null, null, self::isInstanceOf(SearchExecutionContext::class))
            ->willReturn(new SearchReindexResult('job-empty', 0, 0));

        $response = $this->controller(
            $coordinator,
            $this->createMock(SearchReindexDispatcherInterface::class),
            $resolver,
        )(new Request(content: 'null'));

        self::assertSame(200, $response->getStatusCode());
    }

    private function controller(
        SearchReindexCoordinatorInterface $coordinator,
        SearchReindexDispatcherInterface $dispatcher,
        SearchExecutionContextResolverInterface $resolver,
    ): SearchReindexApiController {
        $serializer = new SearchReindexResultSerializer();

        return new SearchReindexApiController(
            $coordinator,
            $serializer,
            $dispatcher,
            new SearchReindexDispatchResultSerializer($serializer),
            $resolver,
        );
    }
}
