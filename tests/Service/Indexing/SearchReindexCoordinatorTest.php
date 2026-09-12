<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service\Indexing;

use App\Searching\Provider\Backend\SearchNullProvider;
use App\Searching\Service\Indexing\SearchDocumentIndexer;
use App\Searching\Service\Indexing\SearchReindexCoordinator;
use App\Searching\Service\Registry\SearchableResourceRegistry;
use App\Searching\Tests\Fixture\FakeSearchableDocumentProvider;
use PHPUnit\Framework\TestCase;

final class SearchReindexCoordinatorTest extends TestCase
{
    public function testItReindexesMatchingProviders(): void
    {
        $registry = new SearchableResourceRegistry();
        $registry->add(new FakeSearchableDocumentProvider('cataloging', 'product'));
        $registry->add(new FakeSearchableDocumentProvider('messaging', 'message'));

        $coordinator = new SearchReindexCoordinator(
            $registry,
            new SearchDocumentIndexer(new SearchNullProvider()),
        );

        $result = $coordinator->reindex('cataloging', 'product');

        self::assertTrue($result->isSuccessful());
        self::assertSame(1, $result->providerCount);
        self::assertSame(1, $result->documentCount);
    }
}
