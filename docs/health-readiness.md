# Searching health / readiness surface

Searching v0.24 adds a lightweight health and readiness boundary for operational visibility.

## Surfaces

```text
GET /api/search/health
GET /admin/search/health
bin/console searching:health
```

## Indicators

```text
providers              provider availability from registered SearchProviderInterface services
registry               searchable resource count and registered/enabled search index definitions
lifecycle              enabled index lifecycle state and last lifecycle errors
indexed_resources      stale/failed/removed source-resource ledger indicators
reindex_backlog        queued/requested/running/failed reindex job indicators
```

## Status model

```text
healthy    no blocking issue detected
degraded   Searching can respond, but operational attention is needed
unhealthy  Searching readiness is materially broken
```

Health is an operational signal only. It must not affect search relevance, permission decisions, producer source-truth validation, or provider selection.
