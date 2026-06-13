# Interfacing Consumption Contract

## Purpose

Interfacing consumes Searching only through the bridge/surface layer. It must render search controls and result screens, but it must not execute provider queries, manage indexes, read reindex jobs, or know Elasticsearch/OpenSearch payloads.

## Stable consumer boundary

Interfacing may depend on these concepts only:

- `SearchBridgeSurfaceConfig`
- `SearchBridgeAutocompleteConfig`
- `SearchBridgeResultPageConfig`
- `SearchBridgeEmptyState`
- `SearchBridgeDegradedState`
- `SearchBridgeRouteHint`
- `SearchSurfaceQuery`
- `SearchSurfaceSuggestionQuery`
- `SearchSurfaceResult`
- `SearchSurfaceResultItem`
- `SearchSurfaceSuggestion`
- `SearchSurfaceFacet`
- `SearchSurfaceHighlight`
- `SearchSurfaceCapability`

Interfacing must not depend on:

- `SearchProviderInterface`
- backend query or suggestion payloads
- `SearchBackendClientInterface`
- index lifecycle classes
- reindex jobs/messages/dispatchers
- indexed-resource ledger classes
- query-log classes
- Elasticsearch/OpenSearch-specific metadata

## Top search UI

Interfacing should ask Bridging for `GET /api/search/bridge/interfacing` and render the top search input from the returned `SearchBridgeSurfaceConfig`.

The top search control should submit to the configured surface endpoint, normally:

```text
GET /api/search/surface?q=...
```

Autocomplete should call:

```text
GET /api/search/surface/suggest?q=...
```

## Result page

The result page renders `SearchSurfaceResult` only. Each result item carries UI-safe fields:

- stable identity
- component/resource labels
- title
- summary
- highlights
- facets
- score when exposed
- route target
- metadata safe for rendering

Route targets must be resolved from route metadata. Interfacing should not invent source URLs from backend index names.

## Empty and degraded states

Empty state and degraded state copy must come from bridge metadata. Interfacing should not create ad hoc fallback bodies when Searching already exposes bridge state.

## Security and stale records

Interfacing receives only hydrated and permission-filtered surface results. It must not bypass `SearchSurfaceProviderInterface` by calling provider/backend services.
