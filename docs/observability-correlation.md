# Searching observability and correlation

Searching keeps observability metadata separate from relevance, permission, and provider logic.

## Execution context

`SearchExecutionContext` carries request/run metadata across query execution, suggestions, backend payloads, query logs, reindex dispatch, queued reindex messages, and reindex results.

Canonical fields:

- `correlationId`
- `requestId`
- `sourceComponent`
- `sourceOperation`
- `actorId`
- `metadata`

HTTP/API controllers resolve the context from headers such as `X-Correlation-Id`, `X-Request-Id`, `X-Source-Component`, and `X-Actor-Id`.

## Search query flow

`SearchQuery` and `SearchSuggestionQuery` carry an optional execution context. The context is propagated into:

- backend query metadata;
- `SearchQueryExecutionTrace`;
- `SearchResult.metadata.execution_context`;
- `SearchResult.metadata.query_trace`;
- `search_query_log` persistence fields.

## Reindex flow

Reindex dispatch and execution accept an optional execution context. The context is propagated into:

- dispatch result metadata;
- `SearchReindexMessage`;
- `SearchReindexResult` metadata;
- `search_reindex_job` fields and serialized admin/API output.

## Boundary rule

Correlation metadata is operational observability. It must not affect relevance scoring, permission decisions, indexed source truth, or provider selection.
