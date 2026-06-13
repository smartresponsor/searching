<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service\Registry;

use App\Searching\Service\Registry\SearchableResourceRegistry;
use App\Searching\Tests\Fixture\FakeSearchableDocumentProvider;
use PHPUnit\Framework\TestCase;

final class SearchableResourceRegistryTest extends TestCase
{
    public function testItIndexesProvidersByComponentAndResource(): void
    {
        $registry = new SearchableResourceRegistry();
        $registry->add(new FakeSearchableDocumentProvider('cataloging', 'product'));
        $registry->add(new FakeSearchableDocumentProvider('messaging', 'message'));

        self::assertCount(2, $registry->all());
        self::assertCount(1, $registry->matching('cataloging', 'product'));
        self::assertSame('cataloging:product', $registry->definitions()[0]->key());
    }
}
