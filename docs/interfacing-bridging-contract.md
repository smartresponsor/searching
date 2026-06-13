# Searching v0.25 — Interfacing / Bridging Surface Contract

## Purpose

`Searching` exposes a small UI-facing contract so `Interfacing` can render global search through `Bridging` without depending on search backend internals.

The surface contract is intentionally narrower than the internal runtime. It exposes only what a UI/bridge needs:

- query input
- result items
- facets
- highlights
- suggestions/autocomplete
- route targets
- degraded/metadata information
- capability information

It does not expose:

- Elasticsearch/OpenSearch payloads
- index lifecycle internals
- reindex jobs
- indexed-resource ledger rows
- provider raw hits
- query log persistence objects

## Runtime flow

```text
Interfacing search UI
  -> Bridging search provider
  -> SearchSurfaceProviderInterface
  -> SearchingSearchSurfaceProvider
  -> SearchQueryExecutorInterface
  -> SearchResult hydration + permission filtering
  -> SearchSurfaceResult
```

Suggestions follow the same boundary:

```text
Interfacing autocomplete
  -> Bridging suggestion provider
  -> SearchSuggestionSurfaceProviderInterface
  -> SearchingSearchSurfaceProvider
  -> SearchSuggestionProviderInterface
  -> list<SearchSurfaceSuggestion>
```

## Bridge-facing services

```php
App\Searching\ServiceInterface\Surface\SearchSurfaceProviderInterface
App\Searching\ServiceInterface\Surface\SearchSuggestionSurfaceProviderInterface
```

Concrete implementation inside Searching:

```php
App\Searching\Service\SearchingSearchSurfaceProvider
```

## Surface values

```text
SearchSurfaceQuery
SearchSurfaceSuggestionQuery
SearchSurfaceResult
SearchSurfaceResultItem
SearchSurfaceFacet
SearchSurfaceHighlight
SearchSurfaceSuggestion
SearchSurfaceCapability
```

These are the only values Bridging/Interfacing should consume for user-facing search.

## API endpoints

The HTTP surface is optional but useful for host apps and UI smoke tests:

```text
GET /api/search/surface
GET /api/search/surface/suggest
GET /api/search/surface/capability
```

The direct internal API endpoints remain available, but Interfacing should prefer the surface endpoints/contracts when building UI.

## Canonical boundary

```text
Searching owns search execution and safe result projection.
Bridging adapts SearchSurface* values to Interfacing contracts.
Interfacing renders UI only.
```

`Interfacing` must not call provider/backend/index/reindex services directly.
