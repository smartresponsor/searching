# Searching v0.25 — Interfacing / Bridging Response Boundary

## Purpose

`Searching` exposes a small UI-facing contract so `Interfacing` can render global search through `Bridging` without depending on search backend internals.

The response boundary is intentionally narrower than the internal runtime. It exposes only what a UI/bridge needs:

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
  -> SearchResponseProviderInterface
  -> SearchResponseProvider
  -> SearchQueryExecutorInterface
  -> SearchResult hydration + permission filtering
  -> SearchResponse
```

Suggestions follow the same boundary:

```text
Interfacing autocomplete
  -> Bridging suggestion provider
  -> SearchSuggestionResponseProviderInterface
  -> SearchResponseProvider
  -> SearchSuggestionProviderInterface
  -> list<SearchSuggestionResponse>
```

## Bridge-facing services

```php
App\Searching\Contract\Query\SearchResponseProviderInterface
App\Searching\Contract\Query\SearchSuggestionResponseProviderInterface
```

Concrete implementation inside Searching:

```php
App\Searching\Provider\SearchResponseProvider
```

## Response values

```text
SearchQueryRequest
SearchSuggestionRequest
SearchResponse
SearchResponseItem
SearchFacetResponse
SearchHighlightResponse
SearchSuggestionResponse
SearchCapability
```

These are the only values Bridging/Interfacing should consume for user-facing search.

## API endpoints

The HTTP response API is optional but useful for host apps and UI smoke tests:

```text
GET /api/search/response
GET /api/search/response/suggest
GET /api/search/capability
```

The direct internal API endpoints remain available, but Interfacing should prefer the response endpoints/contracts when building UI.

## Canonical boundary

```text
Searching owns search execution and safe result projection.
Bridging adapts SearchResponse* values to Interfacing-owned interfaces.
Interfacing renders UI only.
```

`Interfacing` must not call provider/backend/index/reindex services directly.
