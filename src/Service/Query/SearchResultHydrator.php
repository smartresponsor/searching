<?php

declare(strict_types=1);

namespace App\Searching\Service\Query;

use App\Searching\ServiceInterface\Producer\SearchResultItemHydratorInterface;
use App\Searching\ServiceInterface\Query\SearchResultHydratorInterface;
use App\Searching\Value\Result\SearchResultHydrationResult;
use App\Searching\Value\Result\SearchResultItem;

final class SearchResultHydrator implements SearchResultHydratorInterface
{
    /**
     * @var array<string, SearchResultItemHydratorInterface>
     */
    private array $hydrators = [];

    public function add(SearchResultItemHydratorInterface $hydrator): void
    {
        $this->hydrators[$this->key($hydrator->getSearchableComponentName(), $hydrator->getSearchableResourceName())] = $hydrator;
    }

    /**
     * @param list<SearchResultItem> $items
     */
    public function hydrate(array $items): SearchResultHydrationResult
    {
        $hydrated = [];
        $dropped = [];

        foreach ($items as $item) {
            $hydrator = $this->hydrators[$this->key($item->component, $item->resourceType)] ?? null;

            if (!$hydrator instanceof SearchResultItemHydratorInterface) {
                $hydrated[] = $item;
                continue;
            }

            try {
                $refreshed = $hydrator->hydrateSearchResultItem($item);
            } catch (\Throwable $exception) {
                $dropped[] = $this->dropMetadata($item, 'hydration_failed', [
                    'error_class' => $exception::class,
                    'error_message' => $exception->getMessage(),
                ]);
                continue;
            }

            if (!$refreshed instanceof SearchResultItem) {
                $dropped[] = $this->dropMetadata($item, 'source_missing_or_stale');
                continue;
            }

            $hydrated[] = $refreshed;
        }

        return new SearchResultHydrationResult($hydrated, $dropped, count($items));
    }

    private function key(string $component, string $resourceType): string
    {
        return strtolower($component).'::'.strtolower($resourceType);
    }

    /**
     * @param array<string, mixed> $extra
     *
     * @return array<string, mixed>
     */
    private function dropMetadata(SearchResultItem $item, string $reason, array $extra = []): array
    {
        return [
            'component' => $item->component,
            'resourceType' => $item->resourceType,
            'resourceId' => $item->resourceId,
            'reason' => $reason,
        ] + $extra;
    }
}
