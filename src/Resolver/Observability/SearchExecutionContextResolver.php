<?php

declare(strict_types=1);

namespace App\Searching\Resolver\Observability;

use App\Searching\Contract\Observability\SearchExecutionContextResolverInterface;
use App\Searching\Value\Observability\SearchExecutionContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final readonly class SearchExecutionContextResolver implements SearchExecutionContextResolverInterface
{
    public function __construct(private ?RequestStack $requestStack = null)
    {
    }

    public function resolve(?Request $request = null, ?string $sourceOperation = null, ?string $actorId = null, array $metadata = []): SearchExecutionContext
    {
        $request ??= $this->requestStack?->getCurrentRequest();

        if (!$request instanceof Request) {
            return SearchExecutionContext::create(
                sourceOperation: $sourceOperation,
                actorId: $actorId,
                metadata: $metadata,
            );
        }

        $correlationId = $this->firstHeader($request, ['X-Correlation-Id', 'X-Request-Id', 'X-Trace-Id']);
        $requestId = $this->firstHeader($request, ['X-Request-Id', 'X-Request-ID']);
        $sourceComponent = $this->firstHeader($request, ['X-Source-Component', 'X-SmartResponsor-Component']);
        $actorId ??= $this->firstHeader($request, ['X-Actor-Id', 'X-User-Id']);

        return SearchExecutionContext::create(
            correlationId: $correlationId,
            requestId: $requestId,
            sourceComponent: $sourceComponent,
            sourceOperation: $sourceOperation,
            actorId: $actorId,
            metadata: $metadata + [
                'http_method' => $request->getMethod(),
                'route' => $request->attributes->get('_route'),
            ],
        );
    }

    /**
     * @param list<string> $names
     */
    private function firstHeader(Request $request, array $names): ?string
    {
        foreach ($names as $nameEntity) {
            $value = trim((string) $request->headers->get($nameEntity, ''));
            if ('' !== $value) {
                return $value;
            }
        }

        return null;
    }
}
