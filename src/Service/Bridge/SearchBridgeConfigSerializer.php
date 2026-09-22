<?php

declare(strict_types=1);

namespace App\Searching\Service\Bridge;

use App\Searching\Service\SearchResponseSerializer;
use App\Searching\ValueObject\Bridge\SearchBridgeAutocompleteConfig;
use App\Searching\ValueObject\Bridge\SearchBridgeConfig;
use App\Searching\ValueObject\Bridge\SearchBridgeDegradedState;
use App\Searching\ValueObject\Bridge\SearchBridgeEmptyState;
use App\Searching\ValueObject\Bridge\SearchBridgeResultPageConfig;
use App\Searching\ValueObject\Bridge\SearchBridgeRouteHint;

final readonly class SearchBridgeConfigSerializer
{
    public function __construct(private SearchResponseSerializer $responseSerializer)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function serializeConfig(SearchBridgeConfig $config): array
    {
        return [
            'capability' => $this->responseSerializer->serializeCapability($config->capability),
            'autocomplete' => $this->serializeAutocomplete($config->autocomplete),
            'resultPage' => $this->serializeResultPage($config->resultPage),
            'emptyState' => $this->serializeEmptyState($config->emptyState),
            'degradedState' => $this->serializeDegradedState($config->degradedState),
            'routeHints' => array_map($this->serializeRouteHint(...), $config->routeHints),
            'metadata' => $config->metadata,
        ];
    }

    /** @return array<string, mixed> */
    public function serializeAutocomplete(SearchBridgeAutocompleteConfig $config): array
    {
        return [
            'enabled' => $config->enabled,
            'endpoint' => $config->endpoint,
            'queryParameter' => $config->queryParameter,
            'minQueryLength' => $config->minQueryLength,
            'limit' => $config->limit,
            'placeholder' => $config->placeholder,
            'metadata' => $config->metadata,
        ];
    }

    /** @return array<string, mixed> */
    public function serializeResultPage(SearchBridgeResultPageConfig $config): array
    {
        return [
            'enabled' => $config->enabled,
            'endpoint' => $config->endpoint,
            'routeName' => $config->routeName,
            'routeParameters' => $config->routeParameters,
            'queryParameter' => $config->queryParameter,
            'facetParameter' => $config->facetParameter,
            'pageParameter' => $config->pageParameter,
            'limitParameter' => $config->limitParameter,
            'defaultLimit' => $config->defaultLimit,
            'metadata' => $config->metadata,
        ];
    }

    /** @return array<string, mixed> */
    public function serializeEmptyState(SearchBridgeEmptyState $state): array
    {
        return array_filter([
            'title' => $state->title,
            'message' => $state->message,
            'actionLabel' => $state->actionLabel,
            'actionRouteName' => $state->actionRouteName,
            'actionRouteParameters' => $state->actionRouteParameters,
            'metadata' => $state->metadata,
        ], static fn (mixed $value): bool => null !== $value && [] !== $value);
    }

    /** @return array<string, mixed> */
    public function serializeDegradedState(SearchBridgeDegradedState $state): array
    {
        return [
            'degraded' => $state->degraded,
            'title' => $state->title,
            'message' => $state->message,
            'providerName' => $state->providerName,
            'metadata' => $state->metadata,
        ];
    }

    /** @return array<string, mixed> */
    public function serializeRouteHint(SearchBridgeRouteHint $hint): array
    {
        return array_filter([
            'routeName' => $hint->routeName,
            'routeParameters' => $hint->routeParameters,
            'label' => $hint->label,
            'metadata' => $hint->metadata,
        ], static fn (mixed $value): bool => null !== $value && [] !== $value);
    }
}
