<?php

declare(strict_types=1);

namespace App\Searching\Tests\Controller\Api;

use App\Searching\Contract\Health\SearchHealthCheckerInterface;
use App\Searching\Contract\Indexing\SearchIndexedResourceReaderInterface;
use App\Searching\Contract\Indexing\SearchIndexReaderInterface;
use App\Searching\Contract\Indexing\SearchIndexWriterInterface;
use App\Searching\Contract\Query\SearchQueryLogReaderInterface;
use App\Searching\Contract\Tuning\SearchRelevanceProfileReaderInterface;
use App\Searching\Contract\Tuning\SearchRelevanceProfileWriterInterface;
use App\Searching\Contract\Tuning\SearchSynonymReaderInterface;
use App\Searching\Contract\Tuning\SearchSynonymWriterInterface;
use App\Searching\Controller\Api\SearchHealthApiController;
use App\Searching\Controller\Api\SearchIndexApiController;
use App\Searching\Controller\Api\SearchIndexedResourceApiController;
use App\Searching\Controller\Api\SearchProviderStatusApiController;
use App\Searching\Controller\Api\SearchQueryLogApiController;
use App\Searching\Controller\Api\SearchRelevanceProfileApiController;
use App\Searching\Controller\Api\SearchSynonymApiController;
use App\Searching\Entity\SearchIndexedResourceEntity;
use App\Searching\Entity\SearchIndexEntity;
use App\Searching\Entity\SearchQueryLogEntity;
use App\Searching\Entity\SearchRelevanceProfileEntity;
use App\Searching\Entity\SearchSynonymEntity;
use App\Searching\Provider\Backend\SearchNullProvider;
use App\Searching\Service\Provider\SearchProviderStatusCollector;
use App\Searching\Service\Registry\SearchProviderRegistry;
use App\Searching\Service\Serialization\SearchHealthReportSerializer;
use App\Searching\Service\Serialization\SearchIndexedResourceSerializer;
use App\Searching\Service\Serialization\SearchIndexSerializer;
use App\Searching\Service\Serialization\SearchProviderStatusSerializer;
use App\Searching\Service\Serialization\SearchQueryLogSerializer;
use App\Searching\Service\Serialization\SearchRelevanceProfileSerializer;
use App\Searching\Service\Serialization\SearchSynonymSerializer;
use App\Searching\ValueObject\Health\SearchHealthIndicator;
use App\Searching\ValueObject\Health\SearchHealthReport;
use App\Searching\ValueObject\Indexing\SearchIndexedResourceCriteria;
use App\Searching\ValueObject\Observability\SearchExecutionContext;
use App\Searching\ValueObject\Query\SearchQueryExecutionTrace;
use App\Searching\ValueObject\Query\SearchQueryLogCriteria;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class SearchApiBoundaryCoverageTest extends TestCase
{
    public function testProviderStatusControllerSerializesRegisteredProvider(): void
    {
        $registry = new SearchProviderRegistry();
        $registry->add('null', new SearchNullProvider());
        $controller = new SearchProviderStatusApiController(
            new SearchProviderStatusCollector($registry),
            new SearchProviderStatusSerializer(),
        );

        $response = $controller();
        /** @var array{total: int, providers: array{null: array{status: string}}} $payload */
        $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(1, $payload['total']);
        self::assertSame('disabled', $payload['providers']['null']['status']);
    }

    public function testHealthControllerUsesHealthyAndUnhealthyStatusCodes(): void
    {
        $serializer = new SearchHealthReportSerializer();

        foreach (['healthy' => 200, 'unhealthy' => 503] as $status => $expectedCode) {
            $checker = $this->createMock(SearchHealthCheckerInterface::class);
            $checker->method('check')->willReturn(new SearchHealthReport(
                status: $status,
                indicators: [new SearchHealthIndicator('provider', $status, 'state')],
                checkedAt: new \DateTimeImmutable('2026-09-15T12:00:00+00:00'),
            ));

            $response = (new SearchHealthApiController($checker, $serializer))();
            /** @var array{status: string} $payload */
            $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

            self::assertSame($expectedCode, $response->getStatusCode());
            self::assertSame($status, $payload['status']);
        }
    }

    public function testIndexedResourceControllerBuildsCriteriaAndPayload(): void
    {
        $resource = new SearchIndexedResourceEntity('ordering', 'order', '42');
        $reader = $this->createMock(SearchIndexedResourceReaderInterface::class);
        $reader->expects(self::once())
            ->method('list')
            ->with(self::callback(static fn (SearchIndexedResourceCriteria $criteria): bool => 'ordering' === $criteria->component && 7 === $criteria->limit))
            ->willReturn([$resource]);
        $reader->expects(self::once())->method('count')->willReturn(1);

        $response = (new SearchIndexedResourceApiController(
            $reader,
            new SearchIndexedResourceSerializer(),
            25,
        ))(new Request(['component' => ' ordering ', 'limit' => '7', 'offset' => '2']));

        /** @var array{total: int, limit: int, offset: int, items: list<array{resourceId: string}>} $payload */
        $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame(1, $payload['total']);
        self::assertSame(7, $payload['limit']);
        self::assertSame(2, $payload['offset']);
        self::assertSame('42', $payload['items'][0]['resourceId']);
    }

    public function testQueryLogControllerSerializesReaderResult(): void
    {
        $log = SearchQueryLogEntity::fromTrace(new SearchQueryExecutionTrace(
            query: 'invoice',
            userId: 'user-1',
            vendorId: 'vendor-1',
            providerName: 'null',
            providerTotal: 1,
            returnedTotal: 1,
            deniedCount: 0,
            durationMs: 1.5,
            executedAt: new \DateTimeImmutable('2026-09-15T12:00:00+00:00'),
            executionContext: new SearchExecutionContext('corr-1', 'req-1', 'ordering', 'search'),
        ));
        $reader = $this->createMock(SearchQueryLogReaderInterface::class);
        $reader->expects(self::once())
            ->method('recent')
            ->with(self::callback(static fn (SearchQueryLogCriteria $criteria): bool => 'invoice' === $criteria->query && 4 === $criteria->limit))
            ->willReturn([$log]);
        $reader->expects(self::once())->method('count')->willReturn(1);

        $response = (new SearchQueryLogApiController(
            $reader,
            new SearchQueryLogSerializer(),
            50,
        ))(new Request(['query' => ' invoice ', 'limit' => '4']));

        /** @var array{total: int, limit: int, items: list<array{query: string, correlationId: string}>} $payload */
        $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame(1, $payload['total']);
        self::assertSame(4, $payload['limit']);
        self::assertSame('invoice', $payload['items'][0]['query']);
        self::assertSame('corr-1', $payload['items'][0]['correlationId']);
    }

    public function testIndexControllerCoversListCreateUpdateAndDeleteContracts(): void
    {
        $index = new SearchIndexEntity('Orders', 'elastic', 'orders', 'ordering', 'order');
        $reader = $this->createMock(SearchIndexReaderInterface::class);
        $writer = $this->createMock(SearchIndexWriterInterface::class);
        $serializer = new SearchIndexSerializer();
        $controller = new SearchIndexApiController($reader, $writer, $serializer, 25);

        $reader->method('list')->willReturn([$index]);
        $reader->method('count')->willReturn(1);
        $reader->method('findOne')->willReturnCallback(static fn (string $provider, string $component, string $resourceType): ?SearchIndexEntity => 'missing' === $resourceType ? null : $index);
        $writer->method('upsert')->willReturn($index);
        $writer->method('update')->willReturn($index);

        $list = $controller->list(new Request(['provider' => ' elastic ', 'limit' => '4', 'offset' => '1']));
        /** @var array{total: int, limit: int, offset: int, items: list<array{indexName: string}>} $listPayload */
        $listPayload = json_decode((string) $list->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame(1, $listPayload['total']);
        self::assertSame(4, $listPayload['limit']);
        self::assertSame(1, $listPayload['offset']);
        self::assertSame('orders', $listPayload['items'][0]['indexName']);

        $create = $controller->create(new Request(content: '{"nameEntity":"Orders"}'));
        self::assertSame(201, $create->getStatusCode());

        self::assertSame(400, $controller->update('order', new Request())->getStatusCode());
        self::assertSame(404, $controller->update('missing', new Request(['provider' => 'elastic', 'component' => 'ordering']))->getStatusCode());
        self::assertSame(200, $controller->update('order', new Request(['provider' => 'elastic', 'component' => 'ordering'], content: '{"enabled":false}'))->getStatusCode());

        self::assertSame(400, $controller->delete('order', new Request())->getStatusCode());
        self::assertSame(404, $controller->delete('missing', new Request(['provider' => 'elastic', 'component' => 'ordering']))->getStatusCode());
        self::assertSame(200, $controller->delete('order', new Request(['provider' => 'elastic', 'component' => 'ordering']))->getStatusCode());
    }

    public function testRelevanceProfileControllerCoversCrudContracts(): void
    {
        $profile = SearchRelevanceProfileEntity::create('Default', ['title' => 2], 'ordering', 'order');
        $reader = $this->createMock(SearchRelevanceProfileReaderInterface::class);
        $writer = $this->createMock(SearchRelevanceProfileWriterInterface::class);
        $reader->method('find')->willReturn([$profile]);
        $reader->method('count')->willReturn(1);
        $reader->method('findOne')->willReturnCallback(static fn (int $id): ?SearchRelevanceProfileEntity => 404 === $id ? null : $profile);
        $writer->method('create')->willReturn($profile);
        $writer->method('update')->willReturn($profile);
        $controller = new SearchRelevanceProfileApiController($reader, $writer, new SearchRelevanceProfileSerializer(), 20);

        $list = $controller->list(new Request(['component' => 'ordering', 'limit' => '3']));
        /** @var array{total: int, limit: int} $listPayload */
        $listPayload = json_decode((string) $list->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame(1, $listPayload['total']);
        self::assertSame(3, $listPayload['limit']);
        self::assertSame(201, $controller->create(new Request(content: '{"nameEntity":"Default"}'))->getStatusCode());
        self::assertSame(404, $controller->update(404, new Request())->getStatusCode());
        self::assertSame(200, $controller->update(1, new Request(content: '{"enabled":false}'))->getStatusCode());
        self::assertSame(404, $controller->delete(404)->getStatusCode());
        self::assertSame(204, $controller->delete(1)->getStatusCode());
    }

    public function testSynonymControllerCoversCrudContracts(): void
    {
        $synonym = SearchSynonymEntity::create('bill', ['invoice'], 'en');
        $reader = $this->createMock(SearchSynonymReaderInterface::class);
        $writer = $this->createMock(SearchSynonymWriterInterface::class);
        $reader->method('find')->willReturn([$synonym]);
        $reader->method('count')->willReturn(1);
        $reader->method('findOne')->willReturnCallback(static fn (int $id): ?SearchSynonymEntity => 404 === $id ? null : $synonym);
        $writer->method('create')->willReturn($synonym);
        $writer->method('update')->willReturn($synonym);
        $controller = new SearchSynonymApiController($reader, $writer, new SearchSynonymSerializer(), 20);

        $list = $controller->list(new Request(['locale' => 'en', 'limit' => '3']));
        /** @var array{total: int, limit: int} $listPayload */
        $listPayload = json_decode((string) $list->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame(1, $listPayload['total']);
        self::assertSame(3, $listPayload['limit']);
        self::assertSame(201, $controller->create(new Request(content: '{"sourceTerm":"bill"}'))->getStatusCode());
        self::assertSame(404, $controller->update(404, new Request())->getStatusCode());
        self::assertSame(200, $controller->update(1, new Request(content: '{"enabled":false}'))->getStatusCode());
        self::assertSame(404, $controller->delete(404)->getStatusCode());
        self::assertSame(204, $controller->delete(1)->getStatusCode());
    }
}
