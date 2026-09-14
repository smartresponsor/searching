<?php

declare(strict_types=1);

namespace App\Searching\Tests\Value;

use App\Searching\Service\Provider\SearchUnavailableBackendClient;
use App\Searching\Value\Flow\SearchOperationLimitRequest;
use App\Searching\Value\Provider\SearchBulkOperation;
use App\Searching\Value\Query\SearchQuery;
use PHPUnit\Framework\TestCase;

final class SearchRuntimeFallbackValueTest extends TestCase
{
    public function testUnavailableBackendExposesDeterministicFallbackBehavior(): void
    {
        $client = new SearchUnavailableBackendClient();

        $client->index('products', '42', ['name' => 'TV']);
        $client->delete('products', '42');
        $client->createIndex('products', ['properties' => []]);
        $client->deleteIndex('products');

        $consumption = new \ArrayObject(['count' => 0]);
        $documents = (static function () use ($consumption): \Generator {
            $consumption['count'] = ((int) $consumption['count']) + 1;
            yield ['documentId' => '42', 'payload' => ['name' => 'TV']];
        })();
        $client->bulkIndex('products', $documents);

        self::assertSame(1, $consumption['count']);
        self::assertFalse($client->indexExists('products'));

        $result = $client->search('products', ['query' => 'tv']);
        self::assertSame(0, $result->total);
        self::assertSame([], $result->items);
        self::assertSame('unavailable', $result->metadata['provider_mode']);
        self::assertSame('products', $result->metadata['index']);

        $status = $client->getStatus('elastic', [
            'enabled' => true,
            'dsn' => 'http://localhost:9200',
            'index_prefix' => 'sr',
        ]);
        self::assertSame('elastic', $status->nameEntity);
        self::assertFalse($status->available);
        self::assertSame('unavailable', $status->status);
        self::assertTrue($status->metadata['enabled']);
        self::assertTrue($status->metadata['dsnConfigured']);
        self::assertSame('sr', $status->metadata['indexPrefix']);

        $defaultStatus = $client->getStatus('null');
        self::assertFalse($defaultStatus->metadata['enabled']);
        self::assertFalse($defaultStatus->metadata['dsnConfigured']);
        self::assertNull($defaultStatus->metadata['indexPrefix']);
    }

    public function testBulkOperationValidatesOperationAndSerializes(): void
    {
        $operation = new SearchBulkOperation(
            operation: 'index',
            indexName: 'products',
            documentId: '42',
            payload: ['name' => 'TV'],
        );

        self::assertSame([
            'operation' => 'index',
            'indexName' => 'products',
            'documentId' => '42',
            'payload' => ['name' => 'TV'],
        ], $operation->toArray());

        $delete = new SearchBulkOperation('delete', 'products', '42');
        self::assertSame('delete', $delete->operation);
        self::assertSame([], $delete->payload);

        $this->expectException(\InvalidArgumentException::class);
        new SearchBulkOperation('update', 'products', '42');
    }

    public function testOperationLimitRequestBuildsStableIdentityCostAndMetadata(): void
    {
        $search = SearchOperationLimitRequest::forSearchQuery(new SearchQuery(
            query: ' overdue invoice ',
            components: ['billing'],
            resourceTypes: ['invoice'],
            limit: 50,
            tenantId: 'tenant-1',
            userId: 'user-7',
        ));

        self::assertSame('search.query', $search->operation);
        self::assertSame('user-7:tenant-1:overdue invoice', $search->identity);
        self::assertSame(10, $search->cost);
        self::assertSame(['billing'], $search->metadata['components']);
        self::assertSame(50, $search->metadata['limit']);

        $targeted = SearchOperationLimitRequest::forReindexDispatch(
            component: 'catalog',
            resourceType: 'product',
            changedSince: new \DateTimeImmutable('2026-09-01T00:00:00+00:00'),
            requestedBy: 'ops',
        );
        self::assertSame('ops:catalog:product', $targeted->identity);
        self::assertSame(20, $targeted->cost);
        self::assertSame('2026-09-01T00:00:00+00:00', $targeted->metadata['changed_since']);

        $global = SearchOperationLimitRequest::forReindexDispatch();
        self::assertSame('anonymous', $global->identity);
        self::assertSame(100, $global->cost);

        $this->expectException(\InvalidArgumentException::class);
        new SearchOperationLimitRequest('invalid', cost: 0);
    }
}
