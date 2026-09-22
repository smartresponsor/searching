<?php

declare(strict_types=1);

namespace App\Searching\Controller\Api;

use App\Searching\Contract\Observability\SearchExecutionContextResolverInterface;
use App\Searching\Contract\Query\SearchSuggestionProviderInterface;
use App\Searching\Service\Serialization\SearchResultSerializer;
use App\Searching\ValueObject\Query\SearchSuggestionQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Defines the search suggestion api controller responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchSuggestionApiController
{
    public function __construct(
        private SearchSuggestionProviderInterface $suggestionProvider,
        private SearchResultSerializer $searchResultSerializer,
        private SearchExecutionContextResolverInterface $executionContextResolver,
    ) {
    }

    #[Route('/api/search/suggest', name: 'searching_api_search_suggest', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $query = trim((string) $request->query->get('q', ''));
        $limit = max(1, min(50, $request->query->getInt('limit', 10)));
        $actorId = $this->nullableString($request->query->get('userId', $request->query->get('user_id')));
        $executionContext = $this->executionContextResolver->resolve($request, 'search.suggest', $actorId);
        $suggestions = $this->suggestionProvider->suggestByQuery(new SearchSuggestionQuery(
            query: $query,
            components: $this->csv($request->query->get('components')),
            resourceTypes: $this->csv($request->query->get('resourceTypes', $request->query->get('resources'))),
            filters: [],
            limit: $limit,
            locale: $this->nullableString($request->query->get('locale')),
            vendorId: $this->nullableString($request->query->get('vendorId', $request->query->get('vendor_id'))),
            userId: $actorId,
            includeSynonyms: $request->query->getBoolean('synonyms', true),
            includeFuzzy: $request->query->getBoolean('fuzzy', true),
            executionContext: $executionContext,
        ));

        return new JsonResponse([
            'query' => $query,
            'limit' => $limit,
            'suggestions' => array_map($this->searchResultSerializer->serializeSuggestion(...), $suggestions),
            'metadata' => [
                'execution_context' => $executionContext->toMetadata(),
            ],
        ]);
    }

    /**
     * @return list<string>
     */
    private function csv(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map(
                static fn (mixed $item): string => is_scalar($item) ? (string) $item : '',
                $value,
            ), static fn (string $item): bool => '' !== trim($item)));
        }

        if (!is_string($value) || '' === trim($value)) {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn (string $item): string => trim($item),
            explode(',', $value),
        ), static fn (string $item): bool => '' !== $item));
    }

    private function nullableString(mixed $value): ?string
    {
        return is_string($value) && '' !== trim($value) ? trim($value) : null;
    }
}
