<?php

declare(strict_types=1);

namespace App\Searching\Factory;

use App\Searching\Service\SearchResponseSerializer;
use App\Searching\ValueObject\Result\SearchResponse;
use App\Searching\ValueObject\Result\SearchResultPayload;

/**
 * Defines the search result payload factory responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchResultPayloadFactory
{
    public function __construct(private SearchResponseSerializer $serializer)
    {
    }

    /** @param array<string, mixed>|null $error */
    public function create(string $query, ?SearchResponse $result, ?array $error = null): SearchResultPayload
    {
        $serializedResult = $result ? $this->serializer->serializeResult($result) : null;

        return new SearchResultPayload(
            SearchResultPayload::WORD,
            SearchResultPayload::VIEW_RESULT,
            $this->buildTemplateName('search', 'base'),
            $this->slotMap(),
            $query,
            $serializedResult,
            $error,
            [
                'top.search' => [
                    'action' => '/search/result',
                    'method' => 'GET',
                    'queryName' => 'q',
                    'placeholder' => 'Search products, categories, orders, screens...',
                    'query' => $query,
                ],
                'left.panel' => [
                    'facets' => $serializedResult['facets'] ?? [],
                    'suggestions' => $serializedResult['suggestions'] ?? [],
                ],
                'main.body' => [
                    'result' => $serializedResult,
                ],
                'right.panel' => [
                    'metadata' => $serializedResult['metadata'] ?? [],
                    'stats' => $this->stats($serializedResult),
                ],
            ],
        );
    }

    /**
     * @param array<string, mixed>|null $result
     *
     * @return list<array{label: string, value: string}>
     */
    private function stats(?array $result): array
    {
        if (null === $result) {
            return [
                ['label' => 'Hits', 'value' => '0'],
                ['label' => 'Page', 'value' => '1'],
                ['label' => 'Limit', 'value' => '0'],
            ];
        }

        return [
            ['label' => 'Hits', 'value' => $this->scalarString($result, 'total', '0')],
            ['label' => 'Page', 'value' => $this->scalarString($result, 'page', '1')],
            ['label' => 'Limit', 'value' => $this->scalarString($result, 'limit', '0')],
        ];
    }

    /** @param array<string, mixed> $result */
    private function scalarString(array $result, string $key, string $default): string
    {
        $value = $result[$key] ?? $default;

        return is_scalar($value) ? (string) $value : $default;
    }

    /**
     * @return array<string, string>
     */
    private function slotMap(): array
    {
        return [
            'top.search' => 'Search',
            'left.panel' => 'Facets',
            'main.body' => 'Results',
            'right.panel' => 'Stats',
        ];
    }

    private function buildTemplateName(string $subject, string $template): string
    {
        return sprintf('%s/%s.%s', $subject, $template, implode('.', ['html', 'twig']));
    }
}
