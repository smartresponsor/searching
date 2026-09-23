<?php

declare(strict_types=1);

namespace App\Searching\Controller\Api;

use App\Searching\Contract\Observability\SearchExecutionContextResolverInterface;
use App\Searching\Contract\Query\SearchResponseProviderInterface;
use App\Searching\Contract\Query\SearchSuggestionResponseProviderInterface;
use App\Searching\Exception\SearchOperationLimitedException;
use App\Searching\Service\SearchResponseSerializer;
use App\Searching\ValueObject\Query\SearchQueryRequest;
use App\Searching\ValueObject\Query\SearchSuggestionRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Defines the search response api controller responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchResponseApiController
{
    public function __construct(
        private SearchResponseProviderInterface $responseProvider,
        private SearchSuggestionResponseProviderInterface $suggestionProvider,
        private SearchResponseSerializer $serializer,
        private SearchExecutionContextResolverInterface $executionContextResolver,
    ) {
    }

    /**
     * Searches the search through the Searching component query boundary.
     */
    #[Route('/api/search/response', name: 'searching_api_response_search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        if ($this->hasIdentityClaim($request)) {
            return new JsonResponse(['error' => 'untrusted_search_identity'], 400);
        }

        try {
            $result = $this->responseProvider->search(new SearchQueryRequest(
                query: trim((string) $request->query->get('q', '')),
                components: $this->csv($request, 'component'),
                resourceTypes: $this->csv($request, 'resourceType'),
                filters: $this->filters($request),
                page: max(1, $request->query->getInt('page', 1)),
                limit: max(1, $request->query->getInt('limit', 20)),
                locale: $this->nullableString($request, 'locale'),
                vendorId: null,
                userId: null,
                includeHighlights: $request->query->getBoolean('highlights', true),
                includeFacets: $request->query->getBoolean('facets', true),
                executionContext: $this->executionContextResolver->resolve($request, 'search.response.query'),
            ));
        } catch (SearchOperationLimitedException $exception) {
            return $this->limitedResponse($exception);
        }

        return new JsonResponse($this->serializer->serializeResult($result));
    }

    /**
     * Builds suggestions for the suggest through the Searching component query boundary.
     */
    #[Route('/api/search/response/suggest', name: 'searching_api_response_suggest', methods: ['GET'])]
    public function suggest(Request $request): JsonResponse
    {
        if ($this->hasIdentityClaim($request)) {
            return new JsonResponse(['error' => 'untrusted_search_identity'], 400);
        }

        $suggestions = $this->suggestionProvider->suggest(new SearchSuggestionRequest(
            query: trim((string) $request->query->get('q', '')),
            components: $this->csv($request, 'component'),
            resourceTypes: $this->csv($request, 'resourceType'),
            filters: $this->filters($request),
            limit: max(1, min(25, $request->query->getInt('limit', 10))),
            locale: $this->nullableString($request, 'locale'),
            vendorId: null,
            userId: null,
            includeSynonyms: $request->query->getBoolean('synonyms', true),
            includeFuzzy: $request->query->getBoolean('fuzzy', true),
            executionContext: $this->executionContextResolver->resolve($request, 'search.response.suggest'),
        ));

        return new JsonResponse([
            'query' => trim((string) $request->query->get('q', '')),
            'suggestions' => array_map($this->serializer->serializeSuggestion(...), $suggestions),
        ]);
    }

    /**
     * Executes the capability responsibility defined by the Searching component contract.
     */
    #[Route('/api/search/capability', name: 'searching_api_capability', methods: ['GET'])]
    public function capability(): JsonResponse
    {
        return new JsonResponse($this->serializer->serializeCapability($this->responseProvider->getCapability()));
    }

    private function limitedResponse(SearchOperationLimitedException $exception): JsonResponse
    {
        $statusCode = $exception->decision->deferred ? 202 : 429;
        $headers = [];

        if (null !== $exception->decision->retryAfterSeconds) {
            $headers['Retry-After'] = (string) $exception->decision->retryAfterSeconds;
        }

        return new JsonResponse([
            'error' => 'search_response_operation_limited',
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

    private function hasIdentityClaim(Request $request): bool
    {
        foreach (['userId', 'user_id', 'vendorId', 'vendor_id'] as $name) {
            if ($request->query->has($name)) {
                return true;
            }
        }

        return false;
    }

    private function nullableString(Request $request, string $nameEntity): ?string
    {
        $value = trim((string) $request->query->get($nameEntity, ''));

        return '' === $value ? null : $value;
    }
}
