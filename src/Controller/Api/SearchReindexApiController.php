<?php

declare(strict_types=1);

namespace App\Searching\Controller\Api;

use App\Searching\Contract\Indexing\SearchReindexCoordinatorInterface;
use App\Searching\Contract\Indexing\SearchReindexDispatcherInterface;
use App\Searching\Contract\Observability\SearchExecutionContextResolverInterface;
use App\Searching\Service\Serialization\SearchReindexDispatchResultSerializer;
use App\Searching\Service\Serialization\SearchReindexResultSerializer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class SearchReindexApiController
{
    public function __construct(
        private SearchReindexCoordinatorInterface $reindexCoordinator,
        private SearchReindexResultSerializer $reindexResultSerializer,
        private SearchReindexDispatcherInterface $reindexDispatcher,
        private SearchReindexDispatchResultSerializer $dispatchResultSerializer,
        private SearchExecutionContextResolverInterface $executionContextResolver,
    ) {
    }

    #[Route('/api/search/reindex', name: 'searching_api_reindex', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent() ?: '{}', true);
        if (!is_array($payload)) {
            $payload = [];
        }
        /** @var array<string, mixed> $payload */
        $changedSince = $this->changedSinceFromPayload($payload);
        $component = $this->stringOrNull($payload['component'] ?? null);
        $resourceType = $this->stringOrNull($payload['resourceType'] ?? $payload['resource'] ?? null);
        $requestedBy = $this->stringOrNull($payload['requestedBy'] ?? $payload['requested_by'] ?? null);

        $executionContext = $this->executionContextResolver->resolve($request, 'search.reindex', $requestedBy);

        if (($payload['async'] ?? false) === true || ($payload['queued'] ?? false) === true) {
            $dispatchResult = $this->reindexDispatcher->dispatch(
                component: $component,
                resourceType: $resourceType,
                changedSince: $changedSince,
                requestedBy: $requestedBy,
                executionContext: $executionContext,
            );

            $payload = $this->dispatchResultSerializer->serialize($dispatchResult);
            $statusCode = $this->dispatchStatusCode($payload, $dispatchResult->queued);
            $headers = $this->dispatchHeaders($payload);

            return new JsonResponse($payload, $statusCode, $headers);
        }

        $result = $this->reindexCoordinator->reindex(
            component: $component,
            resourceType: $resourceType,
            changedSince: $changedSince,
            executionContext: $executionContext,
        );

        return new JsonResponse($this->reindexResultSerializer->serialize($result));
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, string>
     */
    private function dispatchHeaders(array $payload): array
    {
        $metadata = $payload['metadata'] ?? null;
        if (!is_array($metadata)) {
            return [];
        }

        $operationLimit = $metadata['operation_limit'] ?? null;
        if (!is_array($operationLimit)) {
            return [];
        }

        $retryAfter = $operationLimit['retry_after_seconds'] ?? null;
        if (!is_int($retryAfter)) {
            return [];
        }

        return ['Retry-After' => (string) $retryAfter];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function dispatchStatusCode(array $payload, bool $queued): int
    {
        $metadata = $payload['metadata'] ?? null;
        $operationLimit = is_array($metadata) ? ($metadata['operation_limit'] ?? null) : null;

        if (is_array($operationLimit) && ($operationLimit['allowed'] ?? true) === false) {
            return ($operationLimit['deferred'] ?? false) === true ? 202 : 429;
        }

        return $queued ? 202 : 200;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function changedSinceFromPayload(array $payload): ?\DateTimeImmutable
    {
        $raw = $payload['changedSince'] ?? $payload['changed_since'] ?? null;

        if (!is_string($raw) || '' === trim($raw)) {
            return null;
        }

        return new \DateTimeImmutable($raw);
    }

    private function stringOrNull(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return '' !== $value ? $value : null;
    }
}
