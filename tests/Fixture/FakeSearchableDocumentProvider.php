<?php

declare(strict_types=1);

namespace App\Searching\Tests\Fixture;

use App\Searching\Contract\Producer\SearchableDocumentProviderInterface;
use App\Searching\Value\Document\SearchDocument;

final readonly class FakeSearchableDocumentProvider implements SearchableDocumentProviderInterface
{
    public function __construct(
        private string $component,
        private string $resource,
    ) {
    }

    public function getSearchableResourceName(): string
    {
        return $this->resource;
    }

    public function getSearchableComponentName(): string
    {
        return $this->component;
    }

    public function provideSearchDocuments(?\DateTimeImmutable $changedSince = null): iterable
    {
        unset($changedSince);

        yield new SearchDocument(
            component: $this->component,
            resourceType: $this->resource,
            resourceId: '1',
            title: 'Example',
            summary: null,
            body: null,
            keywords: [],
            facets: [],
            permissions: [],
            locale: 'en',
            tenantId: null,
            ownerId: null,
            routeName: 'example_show',
            routeParameters: ['id' => 1],
            updatedAt: new \DateTimeImmutable(),
        );
    }
}
