<?php

declare(strict_types=1);

namespace App\Searching\Controller\Api;

use App\Searching\Service\Serialization\SearchIndexLifecycleResultSerializer;
use App\Searching\ServiceInterface\Indexing\SearchIndexLifecycleManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class SearchIndexLifecycleApiController
{
    public function __construct(
        private SearchIndexLifecycleManagerInterface $lifecycleManager,
        private SearchIndexLifecycleResultSerializer $serializer,
    ) {
    }

    #[Route('/api/search/index/ensure', name: 'searching_api_index_ensure', methods: ['POST'])]
    public function ensure(Request $request): JsonResponse
    {
        $payload = $this->payload($request);
        $component = $this->stringValue($payload, 'component');
        $resource = $this->stringValue($payload, 'resource');
        $provider = $this->optionalStringValue($payload, 'provider');

        if ('' === $component || '' === $resource) {
            return new JsonResponse(['error' => 'component and resource are required'], 400);
        }

        if (null === $provider || '' === $provider) {
            return new JsonResponse($this->serializer->serializeMany(
                $this->lifecycleManager->ensureForAllProviders($component, $resource),
            ));
        }

        return new JsonResponse($this->serializer->serialize(
            $this->lifecycleManager->ensure($provider, $component, $resource),
        ));
    }

    #[Route('/api/search/index/delete', name: 'searching_api_index_delete', methods: ['POST'])]
    public function delete(Request $request): JsonResponse
    {
        $payload = $this->payload($request);
        $component = $this->stringValue($payload, 'component');
        $resource = $this->stringValue($payload, 'resource');
        $provider = $this->stringValue($payload, 'provider');

        if ('' === $component || '' === $resource || '' === $provider) {
            return new JsonResponse(['error' => 'component, resource, and provider are required'], 400);
        }

        return new JsonResponse($this->serializer->serialize(
            $this->lifecycleManager->delete($provider, $component, $resource),
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Request $request): array
    {
        $decoded = json_decode($request->getContent(), true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function stringValue(array $payload, string $key): string
    {
        $value = $payload[$key] ?? '';

        return is_string($value) ? trim($value) : '';
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function optionalStringValue(array $payload, string $key): ?string
    {
        $value = $payload[$key] ?? null;

        return is_string($value) ? trim($value) : null;
    }
}
