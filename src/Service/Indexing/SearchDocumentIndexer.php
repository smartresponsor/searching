<?php

declare(strict_types=1);

namespace App\Searching\Service\Indexing;

use App\Searching\Contract\Indexing\SearchDocumentIndexerInterface;
use App\Searching\Contract\Indexing\SearchIndexedResourceTrackerInterface;
use App\Searching\Contract\Provider\SearchProviderInterface;
use App\Searching\Normalizer\SearchDocumentNormalizer;
use App\Searching\ValueObject\Document\SearchDocument;

final readonly class SearchDocumentIndexer implements SearchDocumentIndexerInterface
{
    public function __construct(
        private SearchProviderInterface $searchProvider,
        private SearchDocumentNormalizer $documentNormalizer = new SearchDocumentNormalizer(),
        private SearchDocumentFingerprintCalculator $fingerprintCalculator = new SearchDocumentFingerprintCalculator(),
        private SearchIndexedResourceTrackerInterface $indexedResourceTracker = new SearchNullIndexedResourceTracker(),
        private bool $skipUnchangedDocuments = true,
    ) {
    }

    public function index(SearchDocument $document): void
    {
        $fingerprint = $this->fingerprintCalculator->fingerprint($document);

        if ($this->skipUnchangedDocuments && $this->indexedResourceTracker->isCurrent($fingerprint)) {
            $this->indexedResourceTracker->markUnchanged($fingerprint);

            return;
        }

        try {
            $this->searchProvider->index($this->documentNormalizer->normalize($document));
            $this->indexedResourceTracker->markIndexed($fingerprint);
        } catch (\Throwable $throwable) {
            $this->indexedResourceTracker->markFailed($fingerprint, $throwable->getMessage());

            throw $throwable;
        }
    }

    public function bulkIndex(iterable $documents): void
    {
        $normalized = [];
        $fingerprints = [];

        foreach ($documents as $document) {
            $fingerprint = $this->fingerprintCalculator->fingerprint($document);

            if ($this->skipUnchangedDocuments && $this->indexedResourceTracker->isCurrent($fingerprint)) {
                $this->indexedResourceTracker->markUnchanged($fingerprint);

                continue;
            }

            $fingerprints[] = $fingerprint;
            $normalized[] = $this->documentNormalizer->normalize($document);
        }

        if ([] === $normalized) {
            return;
        }

        try {
            $this->searchProvider->bulkIndex($normalized);

            foreach ($fingerprints as $fingerprint) {
                $this->indexedResourceTracker->markIndexed($fingerprint);
            }
        } catch (\Throwable $throwable) {
            foreach ($fingerprints as $fingerprint) {
                $this->indexedResourceTracker->markFailed($fingerprint, $throwable->getMessage());
            }

            throw $throwable;
        }
    }
}
