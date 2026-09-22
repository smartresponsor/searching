<?php

declare(strict_types=1);

namespace App\Searching\Tests\Observability;

use App\Searching\Message\SearchReindexMessage;
use App\Searching\ValueObject\Observability\SearchExecutionContext;
use PHPUnit\Framework\TestCase;

final class SearchReindexMessageContextTest extends TestCase
{
    public function testReindexMessageCarriesExecutionContext(): void
    {
        $context = SearchExecutionContext::create('corr-2', 'req-2', 'administering', 'search.reindex', 'admin');
        $message = new SearchReindexMessage(
            jobKey: 'job-1',
            component: 'cataloging',
            resourceType: 'product',
            executionContext: $context,
        );

        $messageContext = $message->executionContext;
        if (null === $messageContext) {
            self::fail('Expected reindex message execution context.');
        }

        self::assertSame('corr-2', $messageContext->correlationId);
        self::assertSame('administering', $messageContext->sourceComponent);
        self::assertSame('search.reindex', $messageContext->sourceOperation);
    }
}
