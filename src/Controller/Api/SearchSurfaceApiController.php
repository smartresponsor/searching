<?php

declare(strict_types=1);

namespace App\Searching\Controller\Api;

use App\Searching\Exception\SearchOperationLimitedException;
use App\Searching\Service\SearchSurfaceSerializer;
use App\Searching\ServiceInterface\Observability\SearchExecutionContextResolverInterface;
use App\Searching\ServiceInterface\Surface\SearchSuggestionSurfaceProviderInterface;
use App\Searching\ServiceInterface\Surface\SearchSurfaceProviderInterface;
use App\Searching\Value\Surface\SearchSurfaceQuery;
use App\Searching\Value\Surface\SearchSurfaceSuggestionQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class SearchSurfaceApiController
{
    public function __construct(
        private SearchSurfaceProviderInterface $surfaceProvider,
        private SearchSuggestionSurfaceProviderInterface $suggestionProvider,
        private SearchSurfaceSerializer $serializer,
        private SearchExecutionContextResolverInterface $executionContextResolver,
    ) {
    }

    #[Route('/api/search/surface', name: 'searching_api_surface_search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        try {
            $result = $this->surfaceProvider->search(new SearchSurfaceQuery(
                query: trim((string) $request->query->get('q', '')),
                components: $this->csv($request, 'component'),
                resourceTypes: $this->csv($request, 'resourceType'),
                filters: $this->filters($request),
                page: max(1, $request->query->getInt('page', 1)),
                limit: max(1, $request->query->getInt('limit', 20)),
                locale: $this->nullableString($request, 'locale'),
                tenantId: $this->nullableString($request, 'tenantId'),
                userId: $this->nullableString($request, 'userId'),
                includeHighlights: $request->query->getBoolean('highlights', true),
                includeFacets: $request->query->getBoolean('facets', true),
                executionContext: $this->executionContextResolver->resolve($request, 'search.surface.query', $this->nullableString($request, 'userId')),
            ));
        } catch (SearchOperationLimitedException $exception) {
            return $this->limitedResponse($exception);
        }

        return new JsonResponse($this->serializer->serializeResult($result));
    }

    #[Route('/api/search/surface/suggest', name: 'searching_api_surface_suggest', methods: ['GET'])]
    public function suggest(Request $request): JsonResponse
    {
        $suggestions = $this->suggestionProvider->suggest(new SearchSurfaceSuggestionQuery(
            query: trim((string) $request->query->get('q', '')),
            components: $this->csv($request, 'component'),
            resourceTypes: $this->csv($request, 'resourceType'),
            filters: $this->filters($request),
            limit: max(1, min(25, $request->query->getInt('limit', 10))),
            locale: $this->nullableString($request, 'locale'),
            tenantId: $this->nullableString($request, 'tenantId'),
            userId: $this->nullableString($request, 'userId'),
            includeSynonyms: $request->query->getBoolean('synonyms', true),
            includeFuzzy: $request->query->getBoolean('fuzzy', true),
            executionContext: $this->executionContextResolver->resolve($request, 'search.surface.suggest', $this->nullableString($request, 'userId')),
        ));

        return new JsonResponse([
            'query' => trim((string) $request->query->get('q', '')),
            'suggestions' => array_map($this->serializer->serializeSuggestion(...), $suggestions),
        ]);
    }

    #[Route('/api/search/surface/capability', name: 'searching_api_surface_capability', methods: ['GET'])]
    public function capability(): JsonResponse
    {
        return new JsonResponse($this->serializer->serializeCapability($this->surfaceProvider->getCapability()));
    }

    private function limitedResponse(SearchOperationLimitedException $exception): JsonResponse
    {
        $statusCode = $exception->decision->deferred ? 202 : 429;
        $headers = [];

        if (null !== $exception->decision->retryAfterSeconds) {
            $headers['Retry-After'] = (string) $exception->decision->retryAfterSeconds;
        }

        return new JsonResponse([
            'error' => 'search_surface_operation_limited',
            'operation' => $exception->request->operation,
            'decision' => $exception->decision->toMetadata(),
        ], $statusCode, $headers);
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
