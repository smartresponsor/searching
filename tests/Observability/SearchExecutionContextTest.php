<?php

declare(strict_types=1);

namespace App\Searching\Tests\Observability;

use App\Searching\Value\Observability\SearchExecutionContext;
use PHPUnit\Framework\TestCase;

final class SearchExecutionContextTest extends TestCase
{
    public function testItNormalizesAndSerializesContext(): void
    {
        $context = SearchExecutionContext::create(
            correlationId: ' corr-1 ',
            requestId: 'req-1',
            sourceComponent: 'interfacing',
            sourceOperation: 'search.query',
            actorId: 'user-1',
            metadata: ['route' => 'searching_api_search'],
        );

        self::assertSame('corr-1', $context->correlationId);
        self::assertSame('req-1', $context->requestId);
        self::assertSame('interfacing', $context->sourceComponent);
        self::assertSame('search.query', $context->sourceOperation);
        self::assertSame('user-1', $context->actorId);
        $metadata = $context->toMetadata();
        /** @var array{metadata: array{route: string}} $metadata */
        self::assertSame('searching_api_search', $metadata['metadata']['route']);
    }

    public function testItGeneratesCorrelationIdWhenMissing(): void
    {
        $context = SearchExecutionContext::create(sourceOperation: 'search.reindex');

        self::assertStringStartsWith('srch_', $context->correlationId);
        self::assertSame('search.reindex', $context->sourceOperation);
    }
}
