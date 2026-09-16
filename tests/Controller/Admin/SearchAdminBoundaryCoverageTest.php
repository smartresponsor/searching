<?php

declare(strict_types=1);

namespace App\Searching\Tests\Controller\Admin;

use App\Searching\Contract\Health\SearchHealthCheckerInterface;
use App\Searching\Contract\Indexing\SearchIndexedResourceReaderInterface;
use App\Searching\Contract\Indexing\SearchIndexReaderInterface;
use App\Searching\Contract\Indexing\SearchReindexCoordinatorInterface;
use App\Searching\Contract\Indexing\SearchReindexJobReaderInterface;
use App\Searching\Contract\Provider\SearchProviderInterface;
use App\Searching\Contract\Query\SearchQueryLogReaderInterface;
use App\Searching\Contract\Query\SearchResponseProviderInterface;
use App\Searching\Contract\Query\SearchSuggestionResponseProviderInterface;
use App\Searching\Contract\Registry\SearchableResourceRegistryInterface;
use App\Searching\Contract\Tuning\SearchRelevanceProfileReaderInterface;
use App\Searching\Contract\Tuning\SearchSynonymReaderInterface;
use App\Searching\Controller\Admin\SearchBridgeAdminController;
use App\Searching\Controller\Admin\SearchHealthAdminController;
use App\Searching\Controller\Admin\SearchIndexAdminController;
use App\Searching\Controller\Admin\SearchIndexedResourceAdminController;
use App\Searching\Controller\Admin\SearchQueryLogAdminController;
use App\Searching\Controller\Admin\SearchReindexAdminController;
use App\Searching\Controller\Admin\SearchReindexJobAdminController;
use App\Searching\Controller\Admin\SearchRelevanceProfileAdminController;
use App\Searching\Controller\Admin\SearchSynonymAdminController;
use App\Searching\Provider\Bridge\SearchInterfacingBridgeProvider;
use App\Searching\Service\Bridge\SearchBridgeConfigSerializer;
use App\Searching\Service\Provider\SearchProviderStatusCollector;
use App\Searching\Service\Registry\SearchProviderRegistry;
use App\Searching\Service\SearchResponseSerializer;
use App\Searching\Service\Serialization\SearchHealthReportSerializer;
use App\Searching\Service\Serialization\SearchIndexedResourceSerializer;
use App\Searching\Service\Serialization\SearchIndexSerializer;
use App\Searching\Service\Serialization\SearchProviderStatusSerializer;
use App\Searching\Service\Serialization\SearchQueryLogSerializer;
use App\Searching\Service\Serialization\SearchRegistrySerializer;
use App\Searching\Service\Serialization\SearchReindexJobSerializer;
use App\Searching\Service\Serialization\SearchReindexResultSerializer;
use App\Searching\Service\Serialization\SearchRelevanceProfileSerializer;
use App\Searching\Service\Serialization\SearchSynonymSerializer;
use App\Searching\Value\Health\SearchHealthReport;
use App\Searching\Value\Indexing\SearchReindexResult;
use App\Searching\Value\Provider\SearchCapability;
use App\Searching\Value\Provider\SearchProviderStatus;
use App\Searching\Value\Registry\SearchableResourceDefinition;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class SearchAdminBoundaryCoverageTest extends TestCase
{
    public function testHealthBridgeAndIndexAdminBoundaries(): void
    {
        $health = $this->createMock(SearchHealthCheckerInterface::class);
        $health->method('check')->willReturn(new SearchHealthReport('healthy', [], new \DateTimeImmutable('2026-09-16T12:00:00+00:00')));
        $healthResponse = (new SearchHealthAdminController($health, new SearchHealthReportSerializer()))();
        self::assertStringContainsString('"status":"healthy"', (string) $healthResponse->getContent());

        $responseProvider = $this->createMock(SearchResponseProviderInterface::class);
        $responseProvider->method('getCapability')->willReturn(new SearchCapability(true, 'null'));
        $suggestionProvider = $this->createMock(SearchSuggestionResponseProviderInterface::class);
        $bridge = new SearchInterfacingBridgeProvider($responseProvider, $suggestionProvider);
        $bridgeResponse = (new SearchBridgeAdminController($bridge, new SearchBridgeConfigSerializer(new SearchResponseSerializer())))->interfacing();
        self::assertStringContainsString('"bridge":"interfacing"', (string) $bridgeResponse->getContent());

        $indexReader = $this->createMock(SearchIndexReaderInterface::class);
        $indexReader->method('list')->willReturn([]);
        $indexReader->method('count')->willReturn(0);
        $resources = $this->createMock(SearchableResourceRegistryInterface::class);
        $resources->method('definitions')->willReturn([new SearchableResourceDefinition('ordering', 'order', 'ProviderA')]);
        $backend = $this->createMock(SearchProviderInterface::class);
        $backend->method('getStatus')->willReturn(new SearchProviderStatus('null', true, 'available'));
        $registry = new SearchProviderRegistry();
        $registry->add('null', $backend);
        $index = new SearchIndexAdminController($indexReader, new SearchIndexSerializer(), $resources, new SearchProviderStatusCollector($registry), new SearchRegistrySerializer(), new SearchProviderStatusSerializer(), 25);
        $payload = json_decode((string) $index(new Request(['limit' => '5', 'offset' => '1']))->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertIsArray($payload);
        self::assertIsArray($payload['resources']);
        self::assertIsArray($payload['providers']);
        self::assertIsArray($payload['providers']['providers']);
        self::assertIsArray($payload['providers']['providers']['null']);
        self::assertSame(5, $payload['limit']);
        self::assertSame(1, $payload['resources']['total']);
        self::assertSame('available', $payload['providers']['providers']['null']['status']);
    }

    public function testReadOnlyAdminListsUseTheirCriteriaContracts(): void
    {
        $indexedReader = $this->createMock(SearchIndexedResourceReaderInterface::class);
        $indexedReader->method('list')->willReturn([]);
        $indexedReader->method('count')->willReturn(0);
        self::assertStringContainsString('"limit":7', (string) (new SearchIndexedResourceAdminController($indexedReader, new SearchIndexedResourceSerializer(), 11))(new Request(['limit' => '7']))->getContent());

        $queryReader = $this->createMock(SearchQueryLogReaderInterface::class);
        $queryReader->method('recent')->willReturn([]);
        $queryReader->method('count')->willReturn(0);
        self::assertStringContainsString('"limit":8', (string) (new SearchQueryLogAdminController($queryReader, new SearchQueryLogSerializer(), 12))(new Request(['limit' => '8']))->getContent());

        $jobReader = $this->createMock(SearchReindexJobReaderInterface::class);
        $jobReader->method('list')->willReturn([]);
        $jobReader->method('count')->willReturn(0);
        self::assertStringContainsString('"limit":9', (string) (new SearchReindexJobAdminController($jobReader, new SearchReindexJobSerializer(), 13))->list(new Request(['limit' => '9']))->getContent());

        $profileReader = $this->createMock(SearchRelevanceProfileReaderInterface::class);
        $profileReader->method('find')->willReturn([]);
        $profileReader->method('count')->willReturn(0);
        self::assertStringContainsString('"limit":10', (string) (new SearchRelevanceProfileAdminController($profileReader, new SearchRelevanceProfileSerializer(), 14))(new Request(['limit' => '10']))->getContent());

        $synonymReader = $this->createMock(SearchSynonymReaderInterface::class);
        $synonymReader->method('find')->willReturn([]);
        $synonymReader->method('count')->willReturn(0);
        self::assertStringContainsString('"limit":11', (string) (new SearchSynonymAdminController($synonymReader, new SearchSynonymSerializer(), 15))(new Request(['limit' => '11']))->getContent());
    }

    public function testReindexAdminCoversGlobalAndScopedRequests(): void
    {
        $coordinator = $this->createMock(SearchReindexCoordinatorInterface::class);
        $coordinator->expects(self::exactly(2))->method('reindex')->willReturnOnConsecutiveCalls(
            new SearchReindexResult('job-all', 1, 2),
            new SearchReindexResult('job-scoped', 1, 1),
        );
        $controller = new SearchReindexAdminController($coordinator, new SearchReindexResultSerializer());
        self::assertStringContainsString('"jobId":"job-all"', (string) $controller(new Request(request: []))->getContent());
        self::assertStringContainsString('"jobId":"job-scoped"', (string) $controller(new Request(request: [
            'component' => 'ordering',
            'resourceType' => 'order',
            'changedSince' => '2026-09-15T12:00:00+00:00',
        ]))->getContent());
    }
}
