# Searching Flow Control

Searching v0.22 adds a lightweight operation limiting boundary for user-facing search and reindex dispatch.

The boundary is intentionally contract-first:

```text
SearchOperationLimitRequest
  -> SearchOperationLimiterInterface
  -> SearchOperationLimitDecision
```

The default runtime remains safe. `flow_control.enabled: false` uses `SearchNullOperationLimiter`, so local/dev installations are not blocked by missing cache/rate-limiter infrastructure.

When enabled, the bundled `SearchOperationLimiter` provides an in-memory fixed-window guard suitable for local/small host protection and integration tests. Production host applications may replace `SearchOperationLimiterInterface` with Redis, Symfony RateLimiter, API gateway, or Vendor-aware quota logic.

## Operations

Current operation keys:

```text
search.query
search.reindex.dispatch
```

`search.query` protects the user-facing API/query execution boundary.

`search.reindex.dispatch` protects sync and Messenger-backed reindex dispatch so large jobs cannot be spammed through HTTP/CLI entrypoints.

## Configuration

```yaml
searching:
  flow_control:
    enabled: false
    default_mode: reject
    operations:
      search.query:
        limit: 120
        window_seconds: 60
        mode: reject
      search.reindex.dispatch:
        limit: 5
        window_seconds: 300
        mode: defer
```

`mode: reject` returns a rejected decision. API controllers should use HTTP 429 and `Retry-After` when available.

`mode: defer` returns a deferred decision. This is useful for reindex dispatch where the caller can retry later without treating the condition as an application error.

## Canonical boundary

```text
Flow control is an application/operational boundary.
It must not become search relevance, permissions, or backend-provider logic.
```
