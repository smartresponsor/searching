<?php

declare(strict_types=1);

namespace App\Searching\Builder;

use App\Searching\Contract\Observability\SearchExecutionContextResolverInterface;
use App\Searching\Contract\Query\SearchResponseProviderInterface;
use App\Searching\Exception\SearchOperationLimitedException;
use App\Searching\Factory\SearchResultPayloadFactory;
use App\Searching\Value\Query\SearchQueryRequest;
use App\Searching\Value\Result\SearchResultPayload;
use Symfony\Component\HttpFoundation\Request;

final readonly class SearchResultPayloadBuilder
{
    public function __construct(
        private SearchResponseProviderInterface $responseProvider,
        private SearchExecutionContextResolverInterface $executionContextResolver,
        private SearchResultPayloadFactory $payloadFactory,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function build(Request $request): array
    {
        $query = trim((string) $request->query->get('q', ''));

        try {
            $result = $this->responseProvider->search(new SearchQueryRequest(
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
            $payload = $this->payloadFactory->create($query, null, [
                'code' => 'search_operation_limited',
                'operation' => $exception->request->operation,
                'decision' => $exception->decision->toMetadata(),
            ]);

            return $this->toViewingPayload($payload, $exception->decision->deferred ? 202 : 429);
        }

        $payload = $this->payloadFactory->create($query, $result);

        return $this->toViewingPayload($payload);
    }

    /**
     * @return array<string, mixed>
     */
    private function toViewingPayload(SearchResultPayload $payload, int $statusCode = 200): array
    {
        $templateContext = $payload->toTemplateContext();
        $fallbackData = $payload->toFallbackData();

        return [
            '_view' => [
                'surface' => 'search',
                'operation' => $templateContext['view'],
                'component' => 'Searching',
                'intent' => 'surface',
                'format' => 'auto',
            ],
            'locations' => $templateContext['slots'],
            'data' => $templateContext + [
                'fallbackData' => $fallbackData,
                'payloadClass' => $payload::class,
            ],
            'meta' => [
                'source' => 'search_result_payload',
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
