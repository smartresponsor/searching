<?php

declare(strict_types=1);

namespace App\Searching\Provider\Failure;

use App\Failing\Contract\OperationFailureInventoryProviderInterface;
use App\Failing\DTO\OperationFailureInventoryDTO;
use App\Failing\ValueObject\FailureCode;

final class SearchOperationFailureInventoryProvider implements OperationFailureInventoryProviderInterface
{
    public function inventories(): iterable
    {
        $failure = new FailureCode(SearchFailureProvider::INDEX_NOT_FOUND);

        yield new OperationFailureInventoryDTO('PATCH', '/api/search/index/{token}', [$failure]);
        yield new OperationFailureInventoryDTO('DELETE', '/api/search/index/{token}', [$failure]);
    }
}
