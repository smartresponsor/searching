<?php

declare(strict_types=1);

namespace App\Searching\Tests\Unit\Failure;

use App\Failing\Inventory\OperationFailureInventory;
use App\Failing\Registry\FailureRegistry;
use App\Searching\Provider\Failure\SearchFailureProvider;
use App\Searching\Provider\Failure\SearchOperationFailureInventoryProvider;
use PHPUnit\Framework\TestCase;

final class SearchFailureContractProviderTest extends TestCase
{
    public function testSearchIndexNotFoundProducesDeterministicEvidenceForUpdateAndDelete(): void
    {
        $registry = new FailureRegistry([new SearchFailureProvider()]);
        $inventory = new OperationFailureInventory(
            [new SearchOperationFailureInventoryProvider()],
            $registry,
        );

        self::assertSame([
            [
                'method' => 'PATCH',
                'path' => '/api/search/index/{token}',
                'code' => SearchFailureProvider::INDEX_NOT_FOUND,
                'status' => 404,
            ],
            [
                'method' => 'DELETE',
                'path' => '/api/search/index/{token}',
                'code' => SearchFailureProvider::INDEX_NOT_FOUND,
                'status' => 404,
            ],
        ], $inventory->evidence());
    }
}
