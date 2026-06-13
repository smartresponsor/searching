# Searching v0.26 Interfacing bridge adapter contract

`Searching` exposes a bridge-facing surface for `Interfacing` through `Bridging` so the UI does not depend on search provider, index lifecycle, query log, reindex, health, or backend classes.

## Runtime flow

```text
Interfacing UI
  -> Bridging adapter
  -> InterfacingSearchBridgeProviderInterface
  -> SearchSurfaceProviderInterface / SearchSuggestionSurfaceProviderInterface
  -> Searching runtime
```

## Bridge configuration endpoint

```text
GET /api/search/bridge/interfacing
GET /admin/search/bridge/interfacing
```

The payload contains:

```text
capability
autocomplete
resultPage
emptyState
degradedState
routeHints
metadata
```

## Contract rule

Interfacing consumes only bridge/surface DTOs:

```text
SearchBridgeSurfaceConfig
SearchBridgeAutocompleteConfig
SearchBridgeResultPageConfig
SearchBridgeEmptyState
SearchBridgeDegradedState
SearchSurfaceQuery
SearchSurfaceResult
SearchSurfaceSuggestionQuery
SearchSurfaceSuggestion
```

Interfacing must not depend on:

```text
SearchProviderInterface
SearchBackendQuery
SearchIndexLifecycleManager
SearchReindexCoordinator
SearchIndexedResource
SearchQueryLog
ElasticsearchSearchProvider
OpenSearchSearchProvider
```

## Degraded mode

When the active provider is unavailable, the bridge still returns a safe UI contract. Interfacing can render disabled autocomplete, an operational warning, or an empty/degraded state without breaking the shell.
