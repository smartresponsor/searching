<?php

declare(strict_types=1);

namespace App\Searching\Contract\Observability;

use App\Searching\ValueObject\Observability\SearchExecutionContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines the search execution context resolver interface responsibility within the Searching component runtime and its typed boundaries.
 */
interface SearchExecutionContextResolverInterface
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function resolve(?Request $request = null, ?string $sourceOperation = null, ?string $actorId = null, array $metadata = []): SearchExecutionContext;
}
