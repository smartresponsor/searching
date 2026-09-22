<?php

declare(strict_types=1);

namespace App\Searching\Contract\Producer;

use App\Searching\ValueObject\Document\SearchDocument;

/**
 * Defines the searchable document provider interface responsibility within the Searching component runtime and its typed boundaries.
 */
interface SearchableDocumentProviderInterface
{
    public function getSearchableResourceName(): string;

    public function getSearchableComponentName(): string;

    /**
     * @return iterable<SearchDocument>
     */
    public function provideSearchDocuments(?\DateTimeImmutable $changedSince = null): iterable;
}
