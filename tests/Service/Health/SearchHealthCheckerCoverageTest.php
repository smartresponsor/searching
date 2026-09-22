<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service\Health;

use App\Searching\Contract\Indexing\SearchIndexedResourceReaderInterface;
use App\Searching\Contract\Indexing\SearchIndexReaderInterface;
use App\Searching\Contract\Indexing\SearchReindexJobReaderInterface;
use App\Searching\Contract\Provider\SearchProviderInterface;
use App\Searching\Contract\Registry\SearchableResourceRegistryInterface;
use App\Searching\Entity\SearchIndexEntity;
use App\Searching\Service\Health\SearchHealthChecker;
use App\Searching\Service\Provider\SearchProviderStatusCollector;
use App\Searching\Service\Registry\SearchProviderRegistry;
use App\Searching\ValueObject\Indexing\SearchIndexCriteria;
use App\Searching\ValueObject\Indexing\SearchIndexedResourceCriteria;
use App\Searching\ValueObject\Indexing\SearchReindexJobCriteria;
use App\Searching\ValueObject\Provider\SearchProviderStatus;
use App\Searching\ValueObject\Registry\SearchableResourceDefinition;
use PHPUnit\Framework\TestCase;

final class SearchHealthCheckerCoverageTest extends TestCase
{
    public function testHealthyDegradedAndUnhealthyReadinessStates(): void
    {
        $healthy = $this->checker(new SearchProviderStatus('elastic', true, 'ready'), [], [], [0, 0, 0], [0, 0, 0, 0]);
        self::assertSame('healthy', $healthy->check()->status);

        $errorIndex = new SearchIndexEntity('Orders', 'elastic', 'orders', 'ordering', 'order');
        $errorIndex->markLifecycleResult('create', 'error', 'mapping failed');
        $degraded = $this->checker(
            new SearchProviderStatus('elastic', false, 'offline'),
            [new SearchableResourceDefinition('ordering', 'order', 'Provider')],
            [$errorIndex],
            [100, 1, 2],
            [5, 3, 2, 1],
            0,
        );
        $degradedReport = $degraded->check();
        self::assertSame('degraded', $degradedReport->status);
        self::assertSame(['degraded', 'degraded', 'degraded', 'degraded', 'degraded'], array_map(
            static fn ($indicator): string => $indicator->status,
            $degradedReport->indicators,
        ));

        $deleted = new SearchIndexEntity('Orders', 'elastic', 'orders', 'ordering', 'order');
        $deleted->markLifecycleResult('delete', 'deleted');
        $unhealthy = $this->checker(null, [], [$deleted], [0, 0, 0], [0, 0, 0, 0], 1);
        $unhealthyReport = $unhealthy->check();
        self::assertSame('unhealthy', $unhealthyReport->status);
        self::assertFalse($unhealthyReport->isReady());
        self::assertSame('unhealthy', $unhealthyReport->indicators[0]->status);
        self::assertSame('unhealthy', $unhealthyReport->indicators[2]->status);
    }

    /**
     * @param list<SearchableResourceDefinition> $definitions
     * @param list<SearchIndexEntity>            $indexes
     * @param array{int, int, int}               $indexedCounts
     * @param array{int, int, int, int}          $reindexCounts
     */
    private function checker(
        ?SearchProviderStatus $providerStatus,
        array $definitions,
        array $indexes,
        array $indexedCounts,
        array $reindexCounts,
        int $enabledIndexes = 0,
    ): SearchHealthChecker {
        $providers = new SearchProviderRegistry();
        if (null !== $providerStatus) {
            $provider = $this->createMock(SearchProviderInterface::class);
            $provider->method('getStatus')->willReturn($providerStatus);
            $providers->add($providerStatus->nameEntity, $provider);
        }

        $resources = $this->createMock(SearchableResourceRegistryInterface::class);
        $resources->method('definitions')->willReturn($definitions);

        $indexReader = $this->createMock(SearchIndexReaderInterface::class);
        $indexReader->method('count')->willReturnCallback(
            static fn (SearchIndexCriteria $criteria): int => true === $criteria->enabled ? $enabledIndexes : count($indexes),
        );
        $indexReader->method('list')->willReturn($indexes);

        $indexedReader = $this->createMock(SearchIndexedResourceReaderInterface::class);
        $indexedReader->method('count')->willReturnCallback(static function (SearchIndexedResourceCriteria $criteria) use ($indexedCounts): int {
            if (true === $criteria->stale) {
                return $indexedCounts[0];
            }

            return 'failed' === $criteria->status ? $indexedCounts[1] : $indexedCounts[2];
        });

        $reindexReader = $this->createMock(SearchReindexJobReaderInterface::class);
        $reindexReader->method('count')->willReturnCallback(static fn (SearchReindexJobCriteria $criteria): int => match ($criteria->status) {
            'queued' => $reindexCounts[0],
            'requested' => $reindexCounts[1],
            'running' => $reindexCounts[2],
            'failed' => $reindexCounts[3],
            default => 0,
        });

        return new SearchHealthChecker(
            new SearchProviderStatusCollector($providers),
            $resources,
            $indexReader,
            $indexedReader,
            $reindexReader,
            10,
            100,
        );
    }
}
