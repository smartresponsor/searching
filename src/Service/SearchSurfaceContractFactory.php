<?php

declare(strict_types=1);

namespace App\Searching\Service;

use App\Searching\Value\Surface\SearchSurfaceContract;
use App\Searching\Value\Surface\SearchSurfaceResult;

final readonly class SearchSurfaceContractFactory
{
    public function __construct(private SearchSurfaceSerializer $serializer)
    {
    }

    public function create(string $query, ?SearchSurfaceResult $result, ?array $error = null): SearchSurfaceContract
    {
        $serializedResult = $result ? $this->serializer->serializeResult($result) : null;

        return new SearchSurfaceContract(
            SearchSurfaceContract::WORD,
            SearchSurfaceContract::VIEW_RESULT,
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
            ['label' => 'Hits', 'value' => (string) ($result['total'] ?? 0)],
            ['label' => 'Page', 'value' => (string) ($result['page'] ?? 1)],
            ['label' => 'Limit', 'value' => (string) ($result['limit'] ?? 0)],
        ];
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

    private function buildTemplateName(string $surface, string $template): string
    {
        return sprintf('%s/%s.%s', $surface, $template, implode('.', ['html', 'twig']));
    }
}
