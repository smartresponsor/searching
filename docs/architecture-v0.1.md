# Searching Architecture v0.1

Searching is the Smart Responsor component responsible for user-facing business-data search.

Producer components expose `SearchableDocumentProviderInterface` implementations. Searching normalizes those documents, indexes them through `SearchProviderInterface`, executes user queries through `SearchQueryExecutorInterface`, and returns permission-aware search results for Interfacing/API consumers.

## Responsibility split

| Component | Responsibility |
| --- | --- |
| Discovery | System resource/capability discovery |
| Searching | User-facing business-data search |
| Indexing | Technical index lifecycle, if separated |
| Interfacing | UI rendering for search |
| Administering | Search configuration/admin surface |
| Producer components | Export searchable documents |

## MVP providers

- `NullSearchProvider` for disabled/local/test mode.
- `ElasticsearchSearchProvider` placeholder for future Elasticsearch implementation.
- `OpenSearchSearchProvider` placeholder for future OpenSearch implementation.

## v0.3 API and admin surface

Searching exposes a backend-neutral API surface for Interfacing and Administering:

- `GET /api/search` executes a user-facing search query through `SearchQueryExecutorInterface`.
- `GET /api/search/suggest` returns backend-neutral suggestions.
- `GET /api/search/resource` lists discovered producer searchable resources.
- `GET /api/search/provider/status` lists registered search providers and availability.
- `POST /api/search/reindex` starts a bounded reindex operation for all resources or a component/resource pair.
- `GET /admin/search/indexes` exposes resources and provider status for Administering.
- `POST /admin/search/reindex` exposes the same reindex coordinator for Administering forms.

Controllers serialize explicit payload arrays instead of leaking backend provider objects. Interfacing and Administering must consume these contracts and must not call Elasticsearch/OpenSearch directly.

## v0.4 backend-neutral provider layer

The component keeps Elasticsearch/OpenSearch as provider infrastructure, not as the architectural owner of search. The runtime-facing contract remains `SearchProviderInterface`; concrete provider services delegate backend-specific IO to `SearchBackendClientInterface`.

Default behavior is safe unavailable mode:

```text
ElasticsearchSearchProvider
OpenSearchSearchProvider
  -> UnavailableSearchBackendClient
```

This means the providers can be registered and inspected by Administering without requiring a real cluster or client library during early architecture waves. The actual client implementation should later be introduced behind `SearchBackendClientInterface`.

Index naming is centralized in `SearchIndexNameBuilder`:

```text
sr_<component>_<resource_type>
sr_<component>_all
sr_all
```

Document and query payloads are built through explicit mappers:

```text
SearchDocumentPayloadMapper
SearchQueryPayloadMapper
```

Controllers and Interfacing must not build Elasticsearch/OpenSearch payloads directly.



## v0.5 backend-neutral mapping/query layer

This slice adds the contract layer required before wiring a real Elasticsearch/OpenSearch client:

- `SearchIndexMappingBuilderInterface` and `SearchIndexMappingBuilder` build provider-neutral index mapping payloads.
- `SearchBackendQueryBuilderInterface` and `SearchBackendQueryBuilder` convert `SearchQuery` values into backend-ready query payloads.
- `SearchBulkOperationBuilderInterface` and `SearchBulkOperationBuilder` normalize bulk index operations before they reach a backend client.
- `SearchBulkOperation`, `SearchBulkOperationSet`, `SearchBackendQuery`, and `SearchIndexMapping` keep provider communication explicit and testable.

The component still defaults to `UnavailableSearchBackendClient`, so Elasticsearch/OpenSearch can be enabled structurally without creating a hard runtime dependency on a vendor client package.


## v0.6 index lifecycle surface

Searching now exposes a backend-neutral index lifecycle layer for preparing managed search indexes without binding the component to a concrete Elasticsearch/OpenSearch client package.

Added surfaces:

- `SearchIndexLifecycleProviderInterface` for provider-level `indexExists`, `ensureIndex`, and `deleteIndex`;
- `SearchIndexLifecycleManagerInterface` for application/command/API coordination;
- `SearchIndexLifecycleResult` as the serializable operation result;
- `searching:index:lifecycle ensure <component> <resource> [--provider=...]`;
- `searching:index:lifecycle delete <component> <resource> --provider=...`;
- `POST /api/search/index/ensure`;
- `POST /api/search/index/delete`.

The default unavailable backend remains safe: it reports unavailable lifecycle results instead of pretending that a real physical index was created.

## v0.7 incremental document intake

Searching now supports producer-side change events for incremental indexing:

- `SearchDocumentChangedEvent` indexes or refreshes one normalized `SearchDocument`.
- `SearchDocumentRemovedEvent` deletes one source resource from the active search provider.
- `SearchIncrementalIndexerInterface` is the service boundary used by the subscriber and command layer.
- `SearchDocumentChangeSubscriber` listens to both events through Symfony event dispatching.
- `bin/console searching:document:remove <component> <resource> <id>` removes one source resource from the active provider.

Producer components should dispatch these events after their own source record is persisted or removed. Full reindex remains available through `searching:index:rebuild`; incremental intake is for ordinary record-level changes.

## v0.8 permission-aware search

Searching now has a final Symfony-side permission guard after provider search:

- `SearchPermissionCheckerInterface`
- `SearchPermissionFilterInterface`
- `SearchPermissionDecision`
- `SearchPermissionFilterResult`
- `SearchPermissionChecker` default implementation
- `SearchPermissionFilter` final result filter

Search backend filtering is treated as an optimization only. User-facing results must pass the final application-side permission filter before they are serialized for API/UI usage. The default checker uses normalized result metadata such as `visibility`, `tenantId`, `ownerId`, `allowedUserIds`, and `requiredPermissions`. Host applications may replace the checker with Symfony voter/security-aware logic later.

## v0.9 query logging and metrics

Searching treats query logging as an application-level observability responsibility, not as a backend-provider concern. The runtime boundary is `SearchQueryLoggerInterface`; the default implementation is `NullSearchQueryLogger`.

Execution flow:

```text
SearchQueryExecutor
  -> provider search
  -> final permission filter
  -> SearchQueryExecutionTrace
  -> SearchQueryLoggerInterface
```

The trace includes:

```text
query
userId / tenantId
providerName
providerTotal
returnedTotal
deniedCount
durationMs
successful/error fields
providerMetadata
execution metadata
```

`SearchQueryLog::fromTrace()` is the canonical persistence bridge for SQLite/system-state storage under `search_query_log`. A host application may later replace the null logger with a Doctrine-backed logger without changing controllers, providers, Interfacing, or producer components.

## v0.10 query log/metrics surface

Searching now has an optional Doctrine-backed query log writer and a repository-facing reader surface.

Default mode remains safe:

```yaml
searching:
  logging:
    enabled: false
    driver: null
```

When the host application is ready to store search metrics in the system database, it may switch to:

```yaml
searching:
  logging:
    enabled: true
    driver: doctrine
```

The runtime path is intentionally separate from provider/backend concerns:

```text
SearchQueryExecutor
  -> SearchQueryExecutionTrace
  -> SearchQueryLoggerInterface
  -> NullSearchQueryLogger | DoctrineSearchQueryLogger
  -> search_query_log
```

Read surfaces:

```text
GET /api/search/query/log
GET /admin/search/query-logs
bin/console searching:query-log:list
```

Supported filters:

```text
query
provider / providerName
user_id / userId
tenant_id / tenantId
successful
from
to
limit
offset
```

## v0.11 Search tuning management

Searching owns search tuning metadata as a first-class user-facing search responsibility. The initial tuning surface contains:

- synonyms by locale/source term/target terms;
- relevance profiles by component/resource type;
- field weight maps for title/summary/body/keyword-style boosts;
- API and admin JSON surfaces that Administering can consume.

This preserves the boundary:

```text
Administering configures tuning surfaces.
Searching owns tuning contracts, persistence shape, serialization, and API/admin read-write surface.
Elasticsearch/OpenSearch remain infrastructure providers.
```

The tuning entities remain system/internal state and use `search_` table names. Producer components do not own global synonyms or global relevance profiles; they may expose searchable fields, while Searching/Administering decide how those fields are tuned.

## v0.12 Query Tuning Execution

Search tuning is now connected to backend query payload generation. `SearchQueryTuningResolverInterface` resolves enabled synonyms and relevance profiles for a `SearchQuery`; `SearchBackendQueryBuilder` applies synonym expansion as scoring `should` clauses and applies relevance profile field weights before falling back to provider `search_fields`. The generated backend payload also includes compact `_searching.tuning` metadata for audit/debug surfaces.

Canonical flow:

```text
SearchSynonym / SearchRelevanceProfile
  -> SearchQueryTuningResolverInterface
  -> SearchBackendQueryBuilderInterface
  -> SearchProviderInterface
  -> Elasticsearch/OpenSearch backend payload
```

This keeps Administering-owned configuration surfaces operational while preserving Searching as the owner of search contracts and backend-neutral query shaping.



## v0.13 Suggestion/autocomplete layer

Searching now has a dedicated suggestion/autocomplete contract layer. `SearchSuggestionQuery` carries the user query, component/resource restrictions, locale, tenant boundary, and suggestion controls. `SearchSuggestionProviderInterface::suggestByQuery()` delegates to the active `SearchProviderInterface`, while backend providers use `SearchBackendSuggestionBuilderInterface` to produce backend-neutral phrase-prefix/fuzzy/synonym-aware payloads.

The API surface remains stable at `GET /api/search/suggest`, now accepting optional `components`, `resourceTypes`, `locale`, `tenantId`, `userId`, `synonyms`, and `fuzzy` query parameters. Serialization exposes only normalized `SearchSuggestion` values, not backend payloads.

## v0.14 Result hydration and stale-result safety

Search backend hits are not treated as final business truth. Producer components may expose `SearchResultItemHydratorInterface` services tagged as `searching.search_result_hydrator`. The default autoconfiguration tags this interface automatically.

Runtime flow:

```text
SearchProviderInterface::search()
  -> SearchResultHydratorInterface::hydrate()
  -> SearchPermissionFilterInterface::filterResult()
  -> SearchResult serialization
```

A producer-side hydrator refreshes a result item from the source component and returns either a refreshed `SearchResultItem` or `null`. Returning `null` drops the backend hit as stale, deleted, disabled, or otherwise no longer source-valid.

This keeps Elasticsearch/OpenSearch from becoming the access/source-of-truth layer. Backend filtering remains an optimization; result hydration and permission filtering are the final Symfony-side guards before API/UI output.

## v0.15 Document freshness and indexed-resource tracking

Searching now has a dedicated source freshness bridge around `search_indexed_resource`.

The bridge records the normalized document hash, source `updatedAt`, indexed timestamp, and indexing status for every indexed business resource. This lets full reindex and incremental indexing distinguish unchanged records from stale, failed, removed, or newly indexed records without treating the external search backend as the source of truth.

Canonical flow:

```text
SearchDocument
  -> SearchDocumentFingerprintCalculator
  -> SearchDocumentIndexer
  -> SearchProviderInterface
  -> SearchIndexedResourceTrackerInterface
  -> search_indexed_resource
```

Statuses:

```text
indexed
unchanged
failed
removed
pending
```

The search backend remains an infrastructure projection. `search_indexed_resource` is the internal system-state ledger for freshness and reindex coordination.

## v0.16 indexed-resource ledger surface

`search_indexed_resource` is now exposed through a read-only reader, serializer, command, API endpoint, and admin endpoint. Its purpose is operational visibility for freshness/reindex coordination.

Surfaces:

```text
GET /api/search/indexed/resource
GET /admin/search/indexed-resources
bin/console searching:indexed-resource:list
```

Canonical interpretation:

```text
Producer source record = business truth
Search backend hit = external projection
search_indexed_resource = internal freshness/reindex ledger
```

The `stale` flag means the indexed projection is missing or older than the producer source timestamp tracked in the ledger. Removed resources are not treated as stale; they are a distinct terminal ledger state until re-created or re-indexed.

## v0.17 SearchIndex registry

`search_index` is the registry of known index definitions. It is separate from `search_indexed_resource`:

```text
search_index              = registered index definitions
search_indexed_resource   = source-resource freshness ledger
search backend index      = external projection
producer source record    = business truth
```

The registry is exposed through API/admin/console surfaces so Administering can list and manage registered search indexes without coupling to Elasticsearch/OpenSearch internals.

## v0.18 lifecycle registry bridge

The index lifecycle surface is now connected to the `search_index` registry. When `SearchIndexLifecycleManager` executes `ensure` or `delete`, it delegates the backend operation to the lifecycle-capable provider and then calls `SearchIndexLifecycleRegistrySynchronizerInterface`.

The synchronizer records:

```text
provider
component
resourceType
indexName
enabled
lifecycleStatus
lastLifecycleOperation
lastLifecycleAt
lastLifecycleError
```

`delete` does not erase the system definition by default. It marks the registry row disabled with the backend lifecycle result. This keeps historical/administrative visibility while the external backend projection can be removed.

## v0.19 Reindex job persistence bridge

`SearchReindexCoordinator` is now connected to a reindex job tracker. The coordinator creates and updates `SearchReindexJob` records while it performs full or scoped reindex operations.

System-state table:

```text
search_reindex_job
```

Canonical responsibility split:

```text
search_reindex_job       = operational reindex job state
search_index             = registered index definition + lifecycle state
search_indexed_resource  = source-resource freshness ledger
search backend index     = external projection
producer source record   = business truth
```

Supported job statuses:

```text
requested
running
completed
completed_with_errors
failed
```

The API/admin/CLI surfaces expose reindex job history for Administering without coupling Administering to backend provider internals.


## v0.20 Async/queued reindex contract

Searching now has a reindex dispatch boundary so large rebuilds do not have to run inside the current HTTP or CLI request.

Default local/dev behavior remains synchronous and safe:

```yaml
searching:
  indexing:
    reindex:
      dispatch_mode: sync
```

When Symfony Messenger is configured in the host application, the dispatcher can be switched to queued mode:

```yaml
searching:
  indexing:
    reindex:
      dispatch_mode: messenger
```

The async layer is intentionally narrow:

- `SearchReindexDispatcherInterface` is the application-facing dispatch contract.
- `SyncSearchReindexDispatcher` is the safe fallback.
- `MessengerSearchReindexDispatcher` creates a persisted requested job and dispatches `SearchReindexMessage`.
- `SearchReindexMessageHandler` runs that same job through `SearchReindexCoordinator::reindexExistingJob()`.
- `POST /api/search/reindex` accepts `async: true` / `queued: true`.
- `bin/console searching:index:enqueue` dispatches through the configured dispatcher.

This keeps operational job state in `search_reindex_job` while allowing the execution mechanism to be synchronous or queued per host configuration.

## v0.21 Messenger retry / duplicate-dispatch contract

`Searching` now treats queued reindexing as an operational workflow rather than a blind message dispatch. `SearchReindexMessage` carries a stable idempotency key, attempt metadata and max-attempt metadata. `MessengerSearchReindexDispatcher` computes the key before dispatch, checks for an already-open matching job, and reuses the open job instead of enqueueing a duplicate.

The canonical state split is:

```text
SearchReindexMessage      = transport-safe worker request
search_reindex_job        = operational ledger and Administering visibility
Messenger transport       = host-owned delivery/retry/failure infrastructure
SearchReindexCoordinator  = actual reindex executor
```

Host applications own Messenger transport and retry configuration. Searching owns the message contract, handler, duplicate guard, idempotency key builder and job-state marking.

## v0.22 flow control and backpressure

Searching includes a dedicated flow-control boundary for operational protection. It is not a relevance feature and it is not a permissions feature.

```text
Search API / reindex dispatch
  -> SearchOperationLimiterInterface
  -> allowed / rejected / deferred decision
```

The default implementation is safe no-op. The bundled in-memory limiter is intentionally lightweight and replaceable. Host applications may provide a distributed limiter when multi-node production behavior is required.

## v0.23 observability/correlation

`SearchExecutionContext` provides a shared operational context for search and reindex flows. It carries `correlationId`, `requestId`, `sourceComponent`, `sourceOperation`, `actorId`, and metadata.

The context is propagated into `SearchQuery`, `SearchSuggestionQuery`, backend query metadata, `SearchQueryExecutionTrace`, `search_query_log`, reindex dispatch results, `SearchReindexMessage`, `SearchReindexResult`, and `search_reindex_job` serialization.

Correlation is intentionally separated from relevance, permission filtering, and provider/backend decisions.

## v0.24 health / readiness boundary

Searching owns a health/readiness boundary that reports operational signals without becoming runtime business logic.

```text
providers              registered provider availability
registry               searchable resources and index definitions
lifecycle              index lifecycle status/errors
indexed_resources      stale/failed ledger records
reindex_backlog        queued/requested/running/failed job counters
```

Surfaces:

```text
GET /api/search/health
GET /admin/search/health
bin/console searching:health
```

Health is a monitoring/Administering integration surface only. It must not affect search relevance, permission filtering, provider selection, or stale-result validation.

## v0.25 — SearchSurface / Bridging boundary

`Searching` exposes `SearchSurfaceProviderInterface` and `SearchSuggestionSurfaceProviderInterface` for Bridging and Interfacing.

The surface layer maps internal `SearchResult` / `SearchSuggestion` values into stable UI-facing `SearchSurface*` values. It strips raw backend metadata and keeps route targets, facets, highlights, and result summaries available for Interfacing.

Canonical rule:

```text
Interfacing must consume Searching through Bridging/SearchSurface contracts, not through provider, index lifecycle, query log, reindex, or backend classes.
```

## v0.26 Interfacing bridge adapter

`Searching` owns the search runtime and external search surface. `Interfacing` owns rendering. `Bridging` connects them through `InterfacingSearchBridgeProviderInterface` and the `SearchBridgeSurfaceConfig` DTO family.

Canonical flow:

```text
Interfacing UI -> Bridging -> InterfacingSearchBridgeProviderInterface -> SearchSurfaceProviderInterface -> Searching runtime
```

Bridge DTOs are the only sanctioned UI-facing contract for top search, autocomplete, results page route hints, empty state, and degraded state.
