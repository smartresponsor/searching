<?php

declare(strict_types=1);

namespace App\Searching\Tests\Controller\Api;

use App\Searching\Contract\Health\SearchHealthCheckerInterface;
use App\Searching\Contract\Indexing\SearchIndexedResourceReaderInterface;
use App\Searching\Contract\Query\SearchQueryLogReaderInterface;
use App\Searching\Controller\Api\SearchHealthApiController;
use App\Searching\Controller\Api\SearchIndexedResourceApiController;
use App\Searching\Controller\Api\SearchProviderStatusApiController;
use App\Searching\Controller\Api\SearchQueryLogApiController;
use App\Searching\Entity\SearchIndexedResourceEntity;
use App\Searching\Entity\SearchQueryLogEntity;
use App\Searching\Provider\Backend\SearchNullProvider;
use App\Searching\Service\Provider\SearchProviderStatusCollector;
use App\Searching\Service\Registry\SearchProviderRegistry;
use App\Searching\Service\Serialization\SearchHealthReportSerializer;
use App\Searching\Service\Serialization\SearchIndexedResourceSerializer;
use App\Searching\Service\Serialization\SearchProviderStatusSerializer;
use App\Searching\Service\Serialization\SearchQueryLogSerializer;
use App\Searching\Value\Health\SearchHealthIndicator;
use App\Searching\Value\Health\SearchHealthReport;
use App\Searching\Value\Indexing\SearchIndexedResourceCriteria;
use App\Searching\Value\Observability\SearchExecutionContext;
use App\Searching\Value\Query\SearchQueryExecutionTrace;
use App\Searching\Value\Query\SearchQueryLogCriteria;
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
            tenantId: 'tenant-1',
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
}
