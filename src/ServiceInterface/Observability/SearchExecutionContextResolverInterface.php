<?php

declare(strict_types=1);

namespace App\Searching\ServiceInterface\Observability;

use App\Searching\Value\Observability\SearchExecutionContext;
use Symfony\Component\HttpFoundation\Request;

interface SearchExecutionContextResolverInterface
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function resolve(?Request $request = null, ?string $sourceOperation = null, ?string $actorId = null, array $metadata = []): SearchExecutionContext;
}
