<?php

declare(strict_types=1);

namespace App\Searching\Contract\Producer;

use App\Searching\ValueObject\Document\SearchDocument;

interface SearchableDocumentProviderInterface
{
    public function getSearchableResourceName(): string;

    public function getSearchableComponentName(): string;

    /**
     * @return iterable<SearchDocument>
     */
    public function provideSearchDocuments(?\DateTimeImmutable $changedSince = null): iterable;
}
