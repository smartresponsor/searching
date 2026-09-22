<?php

declare(strict_types=1);

namespace App\Searching\Tests\Health;

use App\Searching\Service\Serialization\SearchHealthReportSerializer;
use App\Searching\ValueObject\Health\SearchHealthIndicator;
use App\Searching\ValueObject\Health\SearchHealthReport;
use PHPUnit\Framework\TestCase;

final class SearchHealthReportSerializerTest extends TestCase
{
    public function testSerializesReport(): void
    {
        $serializer = new SearchHealthReportSerializer();
        $report = new SearchHealthReport(
            status: 'degraded',
            indicators: [new SearchHealthIndicator('providers', 'degraded', 'No real backend available.', ['available' => 0])],
            checkedAt: new \DateTimeImmutable('2026-05-27T00:00:00+00:00'),
        );

        $payload = $serializer->serializeReport($report);
        /** @var array{status: string, ready: bool, indicators: list<array{nameEntity: string, metrics: array{available: int}}>} $payload */
        self::assertSame('degraded', $payload['status']);
        self::assertTrue($payload['ready']);
        self::assertSame('providers', $payload['indicators'][0]['nameEntity']);
        self::assertSame(0, $payload['indicators'][0]['metrics']['available']);
    }
}
