<?php

declare(strict_types=1);

namespace App\Searching\Controller\Api;

use App\Searching\Contract\Observability\SearchExecutionContextResolverInterface;
use App\Searching\Contract\Query\SearchQueryExecutorInterface;
use App\Searching\Exception\SearchOperationLimitedException;
use App\Searching\Service\Serialization\SearchResultSerializer;
use App\Searching\ValueObject\Query\SearchQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Defines the search api controller responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchApiController
{
    public function __construct(
        private SearchQueryExecutorInterface $searchQueryExecutor,
        private SearchResultSerializer $searchResultSerializer,
        private SearchExecutionContextResolverInterface $executionContextResolver,
    ) {
    }

    #[Route('/api/search', name: 'searching_api_search', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        if ($this->hasIdentityClaim($request)) {
            return new JsonResponse(['error' => 'untrusted_search_identity'], 400);
        }

        try {
            $result = $this->searchQueryExecutor->execute(new SearchQuery(
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
                executionContext: $this->executionContextResolver->resolve($request, 'search.query'),
            ));
        } catch (SearchOperationLimitedException $exception) {
            $statusCode = $exception->decision->deferred ? 202 : 429;
            $headers = [];

            if (null !== $exception->decision->retryAfterSeconds) {
                $headers['Retry-After'] = (string) $exception->decision->retryAfterSeconds;
            }

            return new JsonResponse([
                'error' => 'search_operation_limited',
                'operation' => $exception->request->operation,
                'decision' => $exception->decision->toMetadata(),
            ], $statusCode, $headers);
        }

        return new JsonResponse($this->searchResultSerializer->serialize($result));
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
