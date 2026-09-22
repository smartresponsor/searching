<?php

declare(strict_types=1);

namespace App\Searching\Service\Indexing;

use App\Searching\Contract\Indexing\SearchIndexLifecycleRegistrySynchronizerInterface;
use App\Searching\Contract\Indexing\SearchIndexWriterInterface;
use App\Searching\ValueObject\Indexing\SearchIndexLifecycleRegistrySyncResult;
use App\Searching\ValueObject\Provider\SearchIndexLifecycleResult;

final readonly class SearchIndexLifecycleRegistrySynchronizer implements SearchIndexLifecycleRegistrySynchronizerInterface
{
    public function __construct(
        private SearchIndexWriterInterface $indexWriter,
    ) {
    }

    public function sync(string $component, string $resourceType, SearchIndexLifecycleResult $result): SearchIndexLifecycleRegistrySyncResult
    {
        $enabled = 'delete' !== $result->operation || !in_array($result->status, ['deleted', 'missing', 'unavailable'], true);
        $reason = $this->metadataString($result, 'reason') ?? $this->metadataString($result, 'error');

        $this->indexWriter->upsert([
            'provider' => $result->providerName,
            'component' => $component,
            'resourceType' => $resourceType,
            'indexName' => $result->indexName,
            'nameEntity' => sprintf('%s %s %s', $result->providerName, $component, $resourceType),
            'enabled' => $enabled,
            'lifecycleOperation' => $result->operation,
            'lifecycleStatus' => $result->status,
            'lifecycleError' => $reason,
        ]);

        return SearchIndexLifecycleRegistrySyncResult::synced($result->status);
    }

    private function metadataString(SearchIndexLifecycleResult $result, string $key): ?string
    {
        if (!array_key_exists($key, $result->metadata)) {
            return null;
        }

        $value = $result->metadata[$key];
        if (null === $value || '' === $value) {
            return null;
        }

        return is_scalar($value) ? (string) $value : json_encode($value, JSON_THROW_ON_ERROR);
    }
}
