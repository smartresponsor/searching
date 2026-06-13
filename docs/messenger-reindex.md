# Searching Messenger Reindex Contract

Searching can dispatch large reindex operations through Symfony Messenger while keeping `sync` mode as the safe local fallback.

## Runtime modes

```yaml
searching:
  indexing:
    reindex:
      dispatch_mode: sync # or messenger
      messenger_max_attempts: 3
      duplicate_guard: true
```

`sync` executes the reindex operation in the current request/command. `messenger` creates a `search_reindex_job` row, marks it as `queued`, and dispatches `SearchReindexMessage`.

## Routing example

```yaml
framework:
  messenger:
    routing:
      'App\Searching\Message\SearchReindexMessage': async
```

The concrete transport name remains host-application owned. Searching only owns the message contract and handler.

## Idempotency

Searching builds a stable idempotency key from:

```text
component
resourceType
changedSince
```

When `duplicate_guard` is enabled, an already `requested`, `queued`, or `running` job with the same key is reused instead of dispatching a duplicate message.

## Failure semantics

The message handler delegates execution to `SearchReindexCoordinator::reindexExistingJob()`. If execution throws, the handler marks the job as `failed` through `SearchReindexJobTrackerInterface` and rethrows the exception so Messenger retry/failure transports can apply host-level policy.

The job ledger remains the operational truth for Administering:

```text
search_reindex_job.status
search_reindex_job.messageAttempts
search_reindex_job.errorMessage
search_reindex_job.lastMessageFailureAt
```

## Canonical boundary

Searching owns the message, handler, job ledger and duplicate guard. The host application owns Messenger transports, retry strategy, failure transport and worker deployment.
