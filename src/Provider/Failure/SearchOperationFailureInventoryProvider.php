<?php

declare(strict_types=1);

namespace App\Searching\Provider\Failure;

use App\Failing\Contract\FailureOperationInventoryProviderInterface;
use App\Failing\DTO\FailureOperationInventoryDTO;
use App\Failing\ValueObject\FailureCode;

final class SearchOperationFailureInventoryProvider implements FailureOperationInventoryProviderInterface
{
    public function inventories(): iterable
    {
        $failure = new FailureCode(SearchFailureProvider::INDEX_NOT_FOUND);

        yield new FailureOperationInventoryDTO('PATCH', '/api/search/index/{token}', [$failure]);
        yield new FailureOperationInventoryDTO('DELETE', '/api/search/index/{token}', [$failure]);
    }
}
