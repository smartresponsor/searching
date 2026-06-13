<?php

declare(strict_types=1);

namespace App\Searching\Service;

use App\Searching\Exception\SearchOperationLimitedException;
use App\Searching\ServiceInterface\Observability\SearchExecutionContextResolverInterface;
use App\Searching\ServiceInterface\Surface\SearchSurfaceProviderInterface;
use App\Searching\Value\Surface\SearchSurfaceContract;
use App\Searching\Value\Surface\SearchSurfaceQuery;
use Symfony\Component\HttpFoundation\Request;

final readonly class SearchResultSurfaceBuilder
{
    public function __construct(
        private SearchSurfaceProviderInterface $surfaceProvider,
        private SearchExecutionContextResolverInterface $executionContextResolver,
        private SearchSurfaceContractFactory $surfaceContractFactory,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function build(Request $request): array
    {
        $query = trim((string) $request->query->get('q', ''));

        try {
            $result = $this->surfaceProvider->search(new SearchSurfaceQuery(
                query: $query,
                components: $this->csv($request, 'component'),
                resourceTypes: $this->csv($request, 'resourceType'),
                filters: $this->filters($request),
                page: max(1, $request->query->getInt('page', 1)),
                limit: max(1, min(100, $request->query->getInt('limit', 20))),
                locale: $this->nullableString($request, 'locale'),
                tenantId: $this->nullableString($request, 'tenantId'),
                userId: $this->nullableString($request, 'userId'),
                includeHighlights: $request->query->getBoolean('highlights', true),
                includeFacets: $request->query->getBoolean('facets', true),
                executionContext: $this->executionContextResolver->resolve($request, 'search.result.page', $this->nullableString($request, 'userId')),
                metadata: [
                    'route' => 'searching_search_result',
                ],
            ));
        } catch (SearchOperationLimitedException $exception) {
            $surface = $this->surfaceContractFactory->create($query, null, [
                'code' => 'search_operation_limited',
                'operation' => $exception->request->operation,
                'decision' => $exception->decision->toMetadata(),
            ]);

            return $this->toViewingPayload($surface, $exception->decision->deferred ? 202 : 429);
        }

        $surface = $this->surfaceContractFactory->create($query, $result);

        return $this->toViewingPayload($surface);
    }

    /**
     * @return array<string, mixed>
     */
    private function toViewingPayload(SearchSurfaceContract $surface, int $statusCode = 200): array
    {
        $templateContext = $surface->toTemplateContext();
        $fallbackData = $surface->toFallbackData();

        return [
            '_view' => [
                'surface' => 'search',
                'operation' => (string) ($templateContext['view'] ?? 'result'),
                'component' => 'Searching',
                'intent' => 'surface',
                'format' => 'auto',
            ],
            'locations' => is_array($templateContext['slots'] ?? null) ? $templateContext['slots'] : [],
            'data' => $templateContext + [
                'fallbackData' => $fallbackData,
                'surfaceClass' => $surface::class,
            ],
            'meta' => [
                'source' => 'search_surface_contract',
                'status_code' => $statusCode,
            ],
        ];
    }

    /**
     * @return list<string>
     */
    private function csv(Request $request, string $nameEntity): array
    {
        $value = (string) $request->query->get($nameEntity, '');

        if ('' === $value) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }

    /**
     * @return array<string, string>
     */
    private function filters(Request $request): array
    {
        $filters = [];

        foreach ($request->query->all('filter') as $nameEntity => $value) {
            if (is_scalar($value)) {
                $filters[(string) $nameEntity] = (string) $value;
            }
        }

        return $filters;
    }

    private function nullableString(Request $request, string $nameEntity): ?string
    {
        $value = trim((string) $request->query->get($nameEntity, ''));

        return '' === $value ? null : $value;
    }
}
