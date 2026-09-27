<?php

declare(strict_types=1);

namespace App\Searching\Provider\Failure;

use App\Failing\Contract\FailureProviderInterface;
use App\Failing\DTO\FailureDefinitionDTO;
use App\Failing\ValueObject\FailureCode;
use App\Failing\ValueObject\FailureType;
use Symfony\Component\HttpFoundation\Response;

final class SearchFailureProvider implements FailureProviderInterface
{
    public const string INDEX_NOT_FOUND = 'search_index_not_found';

    public function definitions(): iterable
    {
        yield new FailureDefinitionDTO(
            new FailureCode(self::INDEX_NOT_FOUND),
            new FailureType('urn:searching:problem:search_index_not_found'),
            Response::HTTP_NOT_FOUND,
            'Search index not found',
        );
    }
}
