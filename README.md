# Searching

Smart Responsor component responsible for user-facing business-data search.

Searching is intentionally separate from Discovery:

- Discovery finds system resources and capabilities.
- Searching executes user-facing search over business records.
- Producer components export searchable document providers.
- Interfacing renders search UI.
- Administering configures searchable surfaces.
- Elasticsearch/OpenSearch is an infrastructure provider, not the owner of the architecture.

## Current slice

This v0.2 slice contains:

- `App\Searching` Symfony bundle skeleton.
- Core SearchDocument/SearchQuery/SearchResult value contracts.
- Search provider abstraction and Null provider.
- Searchable document provider contract for producer components.
- Tagged producer-provider discovery through `searching.searchable_document_provider`.
- Searchable resource registry with component/resource filtering.
- Reindex coordinator skeleton using producer providers.
- Basic commands and API controller skeletons.

## Producer convention

Producer components expose searchable data through services under:

```text
src/Service/Search/*SearchDocumentProvider.php
```

Those classes implement:

```text
App\Searching\Contract\Producer\SearchableDocumentProviderInterface
```

Symfony autoconfiguration tags them as:

```text
searching.searchable_document_provider
```

## Commands

```bash
bin/console searching:provider:status
bin/console searching:index:rebuild
bin/console searching:index:rebuild --component=cataloging --resource=product
bin/console searching:index:rebuild --since="2026-05-01T00:00:00Z"
```

## v0.3 API/Admin surface

Wave v0.3 adds the first useful runtime surface for host integration:

```text
GET  /api/search
GET  /api/search/suggest
GET  /api/search/resource
GET  /api/search/provider/status
POST /api/search/reindex
GET  /admin/search/indexes
POST /admin/search/reindex
```

The routes are declared in YAML and can be imported from `config/routes/search_routes.yaml`.

## v0.4 provider groundwork

Searching now has a backend-neutral provider layer for Elasticsearch/OpenSearch readiness without coupling the component to a concrete client package yet.

Current provider flow:

```text
SearchProviderInterface
  -> SearchElasticsearchProvider / SearchOpenSearchProvider
  -> SearchBackendClientInterface
  -> SearchUnavailableBackendClient by default
```

The unavailable backend is intentionally safe: it reports unavailable status and returns empty search results instead of attempting network access. A real Elasticsearch/OpenSearch client can later replace `SearchBackendClientInterface` without changing controllers, query services, producer providers, or Interfacing/Administering consumers.

New groundwork includes:

```text
- SearchProviderConfiguration
- SearchBackendClientInterface
- SearchIndexNameBuilder
- SearchDocumentPayloadMapper
- SearchQueryPayloadMapper
- SearchAbstractBackendProvider
- SearchUnavailableBackendClient
```



## v0.5 backend-neutral mapping/query layer

This slice adds the contract layer required before wiring a real Elasticsearch/OpenSearch client:

- `SearchIndexMappingBuilderInterface` and `SearchIndexMappingBuilder` build provider-neutral index mapping payloads.
- `SearchBackendQueryBuilderInterface` and `SearchBackendQueryBuilder` convert `SearchQuery` values into backend-ready query payloads.
- `SearchBulkOperationBuilderInterface` and `SearchBulkOperationBuilder` normalize bulk index operations before they reach a backend client.
- `SearchBulkOperation`, `SearchBulkOperationSet`, `SearchBackendQuery`, and `SearchIndexMapping` keep provider communication explicit and testable.

The component still defaults to `SearchUnavailableBackendClient`, so Elasticsearch/OpenSearch can be enabled structurally without creating a hard runtime dependency on a vendor client package.


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

## v0.9 query logging and execution trace

Searching now records a provider-neutral query execution trace around every `SearchQueryExecutor::execute()` call:

- `SearchQueryExecutionTrace` captures query text, user/tenant scope, provider name, provider total, returned total, denied count, duration, success/failure, provider metadata, and execution metadata.
- `SearchQueryLoggerInterface` is the replaceable logging boundary.
- `SearchNullQueryLogger` is the default no-op logger so local/dev runtimes stay safe without a database writer.
- `SearchQueryLog::fromTrace()` prepares SQLite/system-state persistence for `search_query_log` without forcing a concrete repository implementation yet.
- `SearchQueryExecutionTraceSerializer` exposes traces for future Administering/observability surfaces.

The executor also adds compact `query_trace` metadata to returned search results. This lets Interfacing/Administering show timing and counts without reading backend-specific provider objects.

## v0.10 query logs and metrics

Searching can now keep query execution traces in `search_query_log` through an optional Doctrine logger.

Default configuration is safe and non-persistent:

```yaml
searching:
  logging:
    enabled: false
    driver: null
```

Enable system-database persistence only in a host app that has mapped the Searching entities:

```yaml
searching:
  logging:
    enabled: true
    driver: doctrine
    flush_immediately: true
    recent_limit: 50
```

Read surfaces:

```text
GET /api/search/query/log
GET /admin/search/query-logs
bin/console searching:query-log:list --limit=25
```

The log stores query text, user/tenant identifiers, provider name, provider total, returned total, denied count, duration, success/error status, provider metadata, and execution metadata.

## v0.11 tuning surface

Searching now exposes the first Administering-consumable tuning surface:

- `GET /api/search/synonym`
- `POST /api/search/synonym`
- `PATCH /api/search/synonym/{id}`
- `DELETE /api/search/synonym/{id}`
- `GET /api/search/relevance/profile`
- `POST /api/search/relevance/profile`
- `PATCH /api/search/relevance/profile/{id}`
- `DELETE /api/search/relevance/profile/{id}`
- `GET /admin/search/synonyms`
- `GET /admin/search/relevance-profiles`

The tuning model is stored through system-state entities using the `search_` table prefix:

- `search_synonym`
- `search_relevance_profile`

These surfaces are intentionally independent from Elasticsearch/OpenSearch client code. Administering can manage synonyms and relevance profiles through Searching contracts without knowing the active backend provider.

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

## v0.14 result hydration

`Searching` supports producer-side result hydration to avoid returning stale or deleted backend hits.

Producer components can implement:

```php
App\Searching\Contract\Producer\SearchResultItemHydratorInterface
```

Autoconfiguration adds the tag:

```text
searching.search_result_hydrator
```

A hydrator returns a refreshed `SearchResultItem` or `null` when the source record is gone, disabled, or no longer valid. Hydration runs before final permission filtering.

## v0.15

Adds document fingerprinting and indexed-resource tracking through `SearchIndexedResourceTrackerInterface`, `SearchDocumentFingerprintCalculator`, and the expanded `search_indexed_resource` entity.

## v0.16 indexed-resource ledger surface

Searching now exposes a read surface for the internal `search_indexed_resource` ledger. This lets Administering and operational tooling inspect which source records have been indexed, skipped, removed, failed, or are stale.

Read surfaces:

```text
GET /api/search/indexed/resource
GET /admin/search/indexed-resources
bin/console searching:indexed-resource:list
```

Supported filters:

```text
component
resource / resourceType
resourceId / resource_id
status
stale
indexedFrom / indexed_from / from
indexedTo / indexed_to / to
limit
offset
```

The ledger remains system/internal state. It does not replace producer-owned business records and it does not make the search backend the source of truth.

## v0.17 — SearchIndex registry surface

Searching now exposes the `search_index` registry as a first-class system-state surface for Administering/API consumers.

Added surfaces:

```text
GET    /api/search/index
POST   /api/search/index
PATCH  /api/search/index/{resourceType}
DELETE /api/search/index/{resourceType}

GET    /admin/search/indexes

bin/console searching:index:list
```

The registry tracks provider/component/resource index definitions separately from the indexed-resource freshness ledger. The source business record remains owned by the producer component; the backend index remains an external projection; `search_index` is the internal registry of known search index definitions.

## v0.18 - lifecycle registry synchronization

`SearchIndexLifecycleManager` now synchronizes backend index lifecycle results into the internal `search_index` registry. `ensure` and `delete` operations still call the active lifecycle-capable provider, but the system ledger is updated through `SearchIndexLifecycleRegistrySynchronizerInterface` so Administering can see the registered index definition and its latest lifecycle state.

New lifecycle state fields on `SearchIndex`:

```text
lifecycleStatus
lastLifecycleOperation
lastLifecycleAt
lastLifecycleError
```

This preserves the canonical separation:

```text
search_index              = registered index definition and lifecycle state
search_indexed_resource   = indexed source-resource freshness ledger
search backend index      = external projection
producer source record    = business truth
```

## v0.19 Reindex job persistence

Searching now persists reindex job state through `search_reindex_job`.

New surfaces:

```text
GET /api/search/reindex/job
GET /api/search/reindex/job/{jobKey}
GET /admin/search/reindex-jobs

bin/console searching:reindex-job:list
```

The reindex coordinator now writes the lifecycle of a reindex run:

```text
requested → running → completed / completed_with_errors / failed
```

`search_reindex_job` is system-state. It tracks operational reindex attempts; the source producer record remains the business truth, and the search backend index remains only an external projection.


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
- `SearchSyncReindexDispatcher` is the safe fallback.
- `SearchMessengerReindexDispatcher` creates a persisted requested job and dispatches `SearchReindexMessage`.
- `SearchReindexMessageHandler` runs that same job through `SearchReindexCoordinator::reindexExistingJob()`.
- `POST /api/search/reindex` accepts `async: true` / `queued: true`.
- `bin/console searching:index:enqueue` dispatches through the configured dispatcher.

This keeps operational job state in `search_reindex_job` while allowing the execution mechanism to be synchronous or queued per host configuration.

## v0.21 queued reindex hardening

Queued reindex now includes:

- `SearchReindexMessage` idempotency key and attempt metadata;
- `SearchReindexIdempotencyKeyBuilder`;
- `SearchReindexDuplicateGuardInterface`;
- duplicate-dispatch protection for open jobs;
- `queued` / `failed` job marking around Messenger dispatch and handling;
- Messenger routing documentation in `docs/messenger-reindex.md`.

Example host routing:

```yaml
framework:
  messenger:
    routing:
      'App\\Searching\\Message\\SearchReindexMessage': async
```

## v0.22 flow control / backpressure boundary

Searching now has a lightweight contract-first operation limiting boundary:

```text
SearchOperationLimitRequest
  -> SearchOperationLimiterInterface
  -> SearchOperationLimitDecision
```

Protected operation keys:

```text
search.query
search.reindex.dispatch
```

Default runtime remains safe: `flow_control.enabled: false` aliases `SearchOperationLimiterInterface` to `SearchNullOperationLimiter`. When enabled, the bundled in-memory limiter can reject or defer operations and exposes retry metadata for API callers. Production hosts may replace the limiter with Symfony RateLimiter, Redis, API gateway, or tenant-aware quota logic.

See `docs/flow-control.md`.

## v0.23 observability/correlation boundary

Searching now includes `SearchExecutionContext` for request/run correlation. API controllers resolve correlation metadata from headers and propagate it through search queries, suggestions, query traces, backend metadata, query logs, reindex dispatch, queued reindex messages, and reindex job serialization.

This is an operational boundary only: correlation IDs, request IDs, and source-component metadata must not affect relevance, permissions, or provider selection.

## v0.24 health / readiness surface

Searching exposes an operational readiness surface for provider availability, registry consistency, index lifecycle state, indexed-resource freshness, and reindex backlog visibility.

```text
GET /api/search/health
GET /admin/search/health
bin/console searching:health
```

The health report is advisory/operational. It does not affect relevance scoring, access-control decisions, producer source-truth validation, or provider selection.

## v0.25 Search response boundary

`Searching` now exposes a bridge-facing surface contract for Interfacing integration:

```text
SearchResponseProviderInterface
SearchSuggestionResponseProviderInterface
```

Use these interfaces through Bridging for UI consumption. They return `SearchResponse*` values and intentionally hide backend/index/reindex internals.

Surface endpoints:

```text
GET /api/search/response
GET /api/search/response/suggest
GET /api/search/capability
```

See `docs/interfacing-bridging-contract.md`.

## v0.26 Interfacing bridge adapter

`Searching` now exposes a bridge-facing Interfacing adapter contract:

```text
GET /api/search/bridge/interfacing
GET /admin/search/bridge/interfacing
```

The bridge payload includes autocomplete config, result-page route hints, empty/degraded states, and surface capability metadata. `Interfacing` should consume this through `Bridging` and must not depend on backend provider, index lifecycle, ledger, reindex, or query-log internals.

## v0.27 integration seal

Searching now exposes a first stable Interfacing consumption boundary through SearchResponse values and SearchBridge interfaces. Interfacing should consume search through Bridging and must not depend on provider/backend/index/reindex internals.

See:

- `docs/interfacing-consumption.md`
- `docs/final-integration-seal-v0.27.md`
