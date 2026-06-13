# Searching Producer Contract

Producer components expose searchable business records through service classes under:

```text
src/Service/Search/*SearchDocumentProvider.php
```

Each provider implements:

```text
App\Searching\ServiceInterface\Producer\SearchableDocumentProviderInterface
```

The bundle autoconfigures those services with the `searching.searchable_document_provider` tag. Searching then collects them through `SearchableResourceRegistry` and reindexes through `SearchReindexCoordinator`.

Producer responsibilities:

- keep ownership of source business entities;
- emit normalized `SearchDocument` projections;
- provide route name and route parameters for result clicks;
- declare component and resource type names explicitly;
- avoid direct Elasticsearch/OpenSearch coupling.

Searching responsibilities:

- collect searchable providers;
- normalize documents;
- apply provider abstraction;
- execute user-facing queries;
- coordinate reindexing;
- expose UI/API-facing search results.

## v0.7 incremental document intake

Searching now supports producer-side change events for incremental indexing:

- `SearchDocumentChangedEvent` indexes or refreshes one normalized `SearchDocument`.
- `SearchDocumentRemovedEvent` deletes one source resource from the active search provider.
- `SearchIncrementalIndexerInterface` is the service boundary used by the subscriber and command layer.
- `SearchDocumentChangeSubscriber` listens to both events through Symfony event dispatching.
- `bin/console searching:document:remove <component> <resource> <id>` removes one source resource from the active provider.

Producer components should dispatch these events after their own source record is persisted or removed. Full reindex remains available through `searching:index:rebuild`; incremental intake is for ordinary record-level changes.
