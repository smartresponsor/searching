# Searching v0.27 Final Integration Seal

## Seal statement

Searching v0.27 closes the first integration contour for user-facing business search:

```text
Producer components
  -> SearchableDocumentProviderInterface / SearchResultItemHydratorInterface
  -> Searching runtime
  -> SearchResponseProviderInterface
  -> SearchInterfacingBridgeProviderInterface
  -> Bridging
  -> Interfacing UI
```

## Responsibility matrix

| Area | Owner | Consumer | Status |
| --- | --- | --- | --- |
| Business source records | Producer components | Searching | sealed |
| Searchable document normalization | Producer components + Searching | Searching provider layer | sealed |
| Backend provider access | Searching | none outside Searching | sealed |
| Hydration/source-truth validation | Producer components + Searching | SearchResponse | sealed |
| Permission filtering | Searching | SearchResponse | sealed |
| Search UI rendering | Interfacing | User | ready |
| Bridge metadata/config | Searching | Bridging/Interfacing | ready |
| Admin tuning/config | Searching | Administering | ready for integration |
| Runtime observability/health | Searching | Administering/ops | ready |

## Hard boundaries

Interfacing must not consume provider, backend, index lifecycle, reindex, query-log, or indexed-resource classes.

Bridging may adapt Searching to Interfacing, but it should not become the search owner. Bridging is a connection layer.

Administering may configure and inspect Searching, but it should not own query execution or index provider logic.

Discovery may discover search-capable surfaces, but it must not absorb user-facing business-data search.

## Readiness after v0.27

Architecture/business completeness for first integration is high enough to start wiring Interfacing through Bridging.

Remaining work is no longer core architecture expansion. Remaining work should be host/integration proof:

- wire Bridging adapter in the host app;
- render top search from bridge config;
- render autocomplete from surface suggestions;
- render search result page from `SearchResponse`;
- register one producer provider and one hydrator;
- run runtime proof with `SearchNullProvider`, then with a real backend provider.

## Stop condition

Do not keep expanding Searching internals before first Interfacing proof. The next useful work belongs in Bridging/Interfacing integration patches.
