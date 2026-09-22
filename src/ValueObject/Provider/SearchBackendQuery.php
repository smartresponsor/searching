<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Provider;

final readonly class SearchBackendQuery
{
    /**
     * @param array<string, mixed>  $query
     * @param array<string, mixed>  $filters
     * @param array<string, string> $sort
     * @param array<string, mixed>  $highlight
     * @param array<string, mixed>  $aggregations
     * @param array<string, mixed>  $metadata
     */
    public function __construct(
        public array $query,
        public array $filters,
        public array $sort,
        public int $from,
        public int $size,
        public array $highlight = [],
        public array $aggregations = [],
        public array $metadata = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        $payload = [
            'from' => $this->from,
            'size' => $this->size,
            'query' => $this->query,
        ];

        if ([] !== $this->sort) {
            $payload['sort'] = $this->sort;
        }

        if ([] !== $this->highlight) {
            $payload['highlight'] = $this->highlight;
        }

        if ([] !== $this->aggregations) {
            $payload['aggs'] = $this->aggregations;
        }

        if ([] !== $this->metadata) {
            $payload['_searching'] = $this->metadata;
        }

        return $payload;
    }
}
