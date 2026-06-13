<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service\Indexing;

use App\Searching\Service\Indexing\SearchIndexLifecycleManager;
use App\Searching\Service\Provider\SearchIndexNameBuilder;
use App\Searching\Service\Registry\SearchProviderRegistry;
use App\Searching\ServiceInterface\Indexing\SearchIndexLifecycleRegistrySynchronizerInterface;
use App\Searching\ServiceInterface\Provider\SearchIndexLifecycleProviderInterface;
use App\Searching\ServiceInterface\Provider\SearchProviderInterface;
use App\Searching\Value\Document\SearchDocument;
use App\Searching\Value\Indexing\SearchIndexLifecycleRegistrySyncResult;
use App\Searching\Value\Provider\SearchIndexLifecycleResult;
use App\Searching\Value\Provider\SearchProviderResult;
use App\Searching\Value\Provider\SearchProviderStatus;
use App\Searching\Value\Query\SearchQuery;
use App\Searching\Value\Query\SearchSuggestionQuery;
use PHPUnit\Framework\TestCase;

final class SearchIndexLifecycleManagerTest extends TestCase
{
    public function testItEnsuresIndexThroughLifecycleProvider(): void
    {
        $registry = new SearchProviderRegistry();
        $registry->add('fake', new FakeLifecycleProvider());

        $sync = new RecordingLifecycleRegistrySynchronizer();
        $manager = new SearchIndexLifecycleManager($registry, new SearchIndexNameBuilder(), 'sr', $sync);
        $result = $manager->ensure('fake', 'cataloging', 'product');

        self::assertSame('fake', $result->providerName);
        self::assertSame('ensure', $result->operation);
        self::assertSame('created', $result->status);
        self::assertTrue($result->changed);
        self::assertSame('cataloging', $sync->component);
        self::assertSame('product', $sync->resourceType);
        self::assertSame('created', $sync->result?->status);
    }

    public function testItReportsUnavailableForProviderWithoutLifecycleContract(): void
    {
        $registry = new SearchProviderRegistry();
        $registry->add('plain', new PlainProvider());

        $sync = new RecordingLifecycleRegistrySynchronizer();
        $manager = new SearchIndexLifecycleManager($registry, new SearchIndexNameBuilder(), 'sr', $sync);
        $result = $manager->ensure('plain', 'cataloging', 'product');

        self::assertSame('plain', $result->providerName);
        self::assertSame('unavailable', $result->status);
        self::assertFalse($result->changed);
        self::assertSame('unavailable', $sync->result?->status);
    }
}

final class RecordingLifecycleRegistrySynchronizer implements SearchIndexLifecycleRegistrySynchronizerInterface
{
    public ?string $component = null;
    public ?string $resourceType = null;
    public ?SearchIndexLifecycleResult $result = null;

    public function sync(string $component, string $resourceType, SearchIndexLifecycleResult $result): SearchIndexLifecycleRegistrySyncResult
    {
        $this->component = $component;
        $this->resourceType = $resourceType;
        $this->result = $result;

        return SearchIndexLifecycleRegistrySyncResult::synced($result->status);
    }
}

final class FakeLifecycleProvider implements SearchProviderInterface, SearchIndexLifecycleProviderInterface
{
    public function index(SearchDocument $document): void
    {
    }

    public function bulkIndex(iterable $documents): void
    {
    }

    public function delete(string $component, string $resourceType, string $resourceId): void
    {
    }

    public function search(SearchQuery $query): SearchProviderResult
    {
        return new SearchProviderResult(0, []);
    }

    public function suggest(SearchSuggestionQuery $query): array
    {
        return [];
    }

    public function getStatus(): SearchProviderStatus
    {
        return new SearchProviderStatus('fake', true, 'available');
    }

    public function indexExists(string $component, string $resourceType): bool
    {
        return false;
    }

    public function ensureIndex(string $component, string $resourceType): SearchIndexLifecycleResult
    {
        return new SearchIndexLifecycleResult('fake', 'sr_cataloging_product', 'ensure', 'created', true);
    }

    public function deleteIndex(string $component, string $resourceType): SearchIndexLifecycleResult
    {
        return new SearchIndexLifecycleResult('fake', 'sr_cataloging_product', 'delete', 'deleted', true);
    }
}

final class PlainProvider implements SearchProviderInterface
{
    public function index(SearchDocument $document): void
    {
    }

    public function bulkIndex(iterable $documents): void
    {
    }

    public function delete(string $component, string $resourceType, string $resourceId): void
    {
    }

    public function search(SearchQuery $query): SearchProviderResult
    {
        return new SearchProviderResult(0, []);
    }

    public function suggest(SearchSuggestionQuery $query): array
    {
        return [];
    }

    public function getStatus(): SearchProviderStatus
    {
        return new SearchProviderStatus('plain', true, 'available');
    }
}
