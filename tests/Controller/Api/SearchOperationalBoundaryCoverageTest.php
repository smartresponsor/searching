<?php

declare(strict_types=1);

namespace App\Searching\Tests\Controller\Api;

use App\Searching\Contract\Indexing\SearchIndexLifecycleManagerInterface;
use App\Searching\Contract\Indexing\SearchReindexCoordinatorInterface;
use App\Searching\Contract\Indexing\SearchReindexDispatcherInterface;
use App\Searching\Contract\Indexing\SearchReindexJobReaderInterface;
use App\Searching\Contract\Observability\SearchExecutionContextResolverInterface;
use App\Searching\Contract\Registry\SearchableResourceRegistryInterface;
use App\Searching\Controller\Api\SearchIndexLifecycleApiController;
use App\Searching\Controller\Api\SearchReindexApiController;
use App\Searching\Controller\Api\SearchReindexJobApiController;
use App\Searching\Controller\Api\SearchResourceApiController;
use App\Searching\Entity\SearchReindexJobEntity;
use App\Searching\Service\Serialization\SearchIndexLifecycleResultSerializer;
use App\Searching\Service\Serialization\SearchRegistrySerializer;
use App\Searching\Service\Serialization\SearchReindexDispatchResultSerializer;
use App\Searching\Service\Serialization\SearchReindexJobSerializer;
use App\Searching\Service\Serialization\SearchReindexResultSerializer;
use App\Searching\ValueObject\Indexing\SearchReindexDispatchResult;
use App\Searching\ValueObject\Indexing\SearchReindexResult;
use App\Searching\ValueObject\Observability\SearchExecutionContext;
use App\Searching\ValueObject\Provider\SearchIndexLifecycleResult;
use App\Searching\ValueObject\Registry\SearchableResourceDefinition;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class SearchOperationalBoundaryCoverageTest extends TestCase
{
    public function testIndexLifecycleControllerCoversValidationAndProviderModes(): void
    {
        $manager = $this->createMock(SearchIndexLifecycleManagerInterface::class);
        $manager->method('ensureForAllProviders')->willReturn([
            'null' => new SearchIndexLifecycleResult('null', 'ordering_order', 'ensure', 'available', false),
        ]);
        $manager->method('ensure')->willReturn(
            new SearchIndexLifecycleResult('elastic', 'ordering_order', 'ensure', 'available', true),
        );
        $manager->method('delete')->willReturn(
            new SearchIndexLifecycleResult('elastic', 'ordering_order', 'delete', 'deleted', true),
        );

        $controller = new SearchIndexLifecycleApiController($manager, new SearchIndexLifecycleResultSerializer());

        self::assertSame(400, $controller->ensure(new Request(content: '{"component":"ordering"}'))->getStatusCode());
        self::assertSame(400, $controller->delete(new Request(content: '{"component":"ordering","resource":"order"}'))->getStatusCode());

        $all = $controller->ensure(new Request(content: '{"component":" ordering ","resource":" order "}'));
        /** @var array{null: array{status: string}} $allPayload */
        $allPayload = json_decode((string) $all->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame('available', $allPayload['null']['status']);

        $named = $controller->ensure(new Request(content: '{"component":"ordering","resource":"order","provider":" elastic "}'));
        /** @var array{provider: string, changed: bool} $namedPayload */
        $namedPayload = json_decode((string) $named->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame('elastic', $namedPayload['provider']);
        self::assertTrue($namedPayload['changed']);

        $deleted = $controller->delete(new Request(content: '{"component":"ordering","resource":"order","provider":"elastic"}'));
        /** @var array{operation: string} $deletedPayload */
        $deletedPayload = json_decode((string) $deleted->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame('delete', $deletedPayload['operation']);
    }

    public function testResourceAndReindexJobControllersSerializeTheirReadBoundaries(): void
    {
        $registry = $this->createMock(SearchableResourceRegistryInterface::class);
        $registry->method('definitions')->willReturn([
            new SearchableResourceDefinition('ordering', 'order', 'App\\Ordering\\Search\\OrderSearchDocumentProvider'),
        ]);

        $resourceResponse = (new SearchResourceApiController($registry, new SearchRegistrySerializer()))();
        /** @var array{total: int, resources: list<array{key: string}>} $resourcePayload */
        $resourcePayload = json_decode((string) $resourceResponse->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame(1, $resourcePayload['total']);
        self::assertSame('ordering:order', $resourcePayload['resources'][0]['key']);

        $job = new SearchReindexJobEntity('job-1', 'ordering', 'order', 'user-1');
        $reader = $this->createMock(SearchReindexJobReaderInterface::class);
        $reader->method('list')->willReturn([$job]);
        $reader->method('count')->willReturn(1);
        $reader->method('findOne')->willReturnCallback(
            static fn (string $key): ?SearchReindexJobEntity => 'job-1' === $key ? $job : null,
        );

        $controller = new SearchReindexJobApiController($reader, new SearchReindexJobSerializer(), 50);
        $list = $controller->list(new Request(['component' => 'ordering', 'limit' => '7', 'offset' => '2']));
        /** @var array{total: int, limit: int, offset: int, items: list<array{jobKey: string}>} $listPayload */
        $listPayload = json_decode((string) $list->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame([1, 7, 2, 'job-1'], [
            $listPayload['total'],
            $listPayload['limit'],
            $listPayload['offset'],
            $listPayload['items'][0]['jobKey'],
        ]);
        self::assertSame(200, $controller->view('job-1')->getStatusCode());
        self::assertSame(404, $controller->view('missing')->getStatusCode());
    }

    public function testReindexControllerCoversSyncQueuedAndLimitedDispatch(): void
    {
        $context = new SearchExecutionContext('corr-1', 'req-1', 'administering', 'search.reindex', 'user-1');
        $resolver = $this->createMock(SearchExecutionContextResolverInterface::class);
        $resolver->method('resolve')->willReturn($context);

        $coordinator = $this->createMock(SearchReindexCoordinatorInterface::class);
        $coordinator->method('reindex')->willReturn(
            new SearchReindexResult('job-sync', 1, 3, executionContext: $context),
        );

        $dispatcher = $this->createMock(SearchReindexDispatcherInterface::class);
        $dispatcher->method('dispatch')->willReturnOnConsecutiveCalls(
            new SearchReindexDispatchResult('job-queued', 'messenger', true),
            new SearchReindexDispatchResult('job-limited', 'messenger', false, metadata: [
                'operation_limit' => [
                    'allowed' => false,
                    'deferred' => false,
                    'retry_after_seconds' => 9,
                ],
            ]),
        );

        $controller = new SearchReindexApiController(
            $coordinator,
            new SearchReindexResultSerializer(),
            $dispatcher,
            new SearchReindexDispatchResultSerializer(),
            $resolver,
        );

        $sync = $controller(new Request(content: '{"component":" ordering ","resource":" order ","requested_by":" user-1 ","changed_since":"2026-09-15T12:00:00+00:00"}'));
        /** @var array{jobId: string, successful: bool} $syncPayload */
        $syncPayload = json_decode((string) $sync->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame('job-sync', $syncPayload['jobId']);
        self::assertTrue($syncPayload['successful']);

        self::assertSame(202, $controller(new Request(content: '{"async":true}'))->getStatusCode());

        $limited = $controller(new Request(content: '{"queued":true}'));
        self::assertSame(429, $limited->getStatusCode());
        self::assertSame('9', $limited->headers->get('Retry-After'));
    }
}
