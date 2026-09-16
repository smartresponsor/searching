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

    public function testItDropsBlankOptionalValuesAndCanReplaceSourceOperation(): void
    {
        $context = SearchExecutionContext::create(
            correlationId: 'corr-2',
            requestId: '   ',
            sourceComponent: '',
            sourceOperation: null,
            actorId: ' ',
        );

        self::assertNull($context->requestId);
        self::assertNull($context->sourceComponent);
        self::assertNull($context->sourceOperation);
        self::assertNull($context->actorId);
        self::assertSame(['correlation_id' => 'corr-2'], $context->toMetadata());

        $derived = $context->withSourceOperation('search.health');
        self::assertSame('corr-2', $derived->correlationId);
        self::assertSame('search.health', $derived->sourceOperation);
    }
}
