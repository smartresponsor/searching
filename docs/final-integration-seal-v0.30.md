# Searching / Bridging / Interfacing Final Integration Seal v0.30

This document seals the first stable integration contour for user-facing business-data search.

## Ownership

```text
Searching    owns SearchSurface/SearchBridge runtime contracts and search execution.
Bridging     adapts Searching contracts into Interfacing-owned provider interfaces.
Interfacing  owns shell rendering, top-search UI, result page rendering, and safe fallback.
```

## Runtime flow

```text
Interfacing top search / result page
  -> App\Interfacing\ServiceInterface\Interfacing\Search\SearchBridgeProviderInterface
  -> App\Bridging\Service\SearchingInterfacing\SearchingInterfacingSearchBridgeProvider
  -> App\Searching\ServiceInterface\Bridge\InterfacingSearchBridgeProviderInterface
  -> Searching SearchSurface runtime
```

## Allowed consumers

Interfacing may consume only:

```text
SearchBridgeProviderInterface          Interfacing-owned interface
SearchBridge* array payloads           returned through Bridging
SearchSurface* array payloads          serialized by Searching/Bridging
/interfacing/search                    result page
/interfacing/search/suggest            autocomplete endpoint
/interfacing/search/config             UI config endpoint
```

Interfacing must not consume:

```text
SearchProviderInterface
SearchBackendClientInterface
SearchBackendQuery / SearchBackendSuggestion payloads
SearchIndexLifecycle* classes
SearchReindex* jobs/messages/dispatchers
SearchIndexedResource* ledger classes
SearchQueryLog* classes
Elasticsearch/OpenSearch implementation metadata
```

## Host application installation order

1. Register the Interfacing component and import Interfacing routes/services.
2. Register the Searching component and import `Searching/config/routes/searching.yaml` if the host needs Searching API/admin surfaces.
3. Register the Bridging component and import `Bridging/config/component/services_searching_interfacing.yaml` after Interfacing's fallback alias.
4. Confirm that Bridging overrides `App\Interfacing\ServiceInterface\Interfacing\Search\SearchBridgeProviderInterface`.
5. Confirm `/interfacing/search`, `/interfacing/search/suggest`, and `/interfacing/search/config` remain renderable even if Searching provider state is degraded.

## Required files

Searching:

```text
src/ServiceInterface/Bridge/InterfacingSearchBridgeProviderInterface.php
src/Service/Bridge/InterfacingSearchBridgeProvider.php
src/Service/Bridge/SearchBridgeSurfaceConfigSerializer.php
src/Contract/SearchInterfacingBridgeSurfaceContract.php
docs/interfacing-bridging-contract.md
docs/interfacing-bridge-adapter.md
docs/interfacing-consumption.md
```

Bridging:

```text
config/component/services_searching_interfacing.yaml
src/Service/SearchingInterfacing/SearchingInterfacingSearchBridgeProvider.php
src/ServiceInterface/SearchingInterfacing/SearchingInterfacingSearchBridgeProviderInterface.php
docs/searching-interfacing/bridge-contract.md
docs/searching-interfacing/search-bridge-runtime-wiring.md
```

Interfacing:

```text
src/ServiceInterface/Interfacing/Search/SearchBridgeProviderInterface.php
src/Service/Interfacing/Search/NullSearchBridgeProvider.php
src/Presentation/Controller/Interfacing/SearchBridgeController.php
templates/interfacing/search/results.html.twig
docs/search/search-bridge-fallback.md
docs/interfacing/search/bridged-search-consumption.md
```

## Final seal statement

Searching is integrated into Interfacing through Bridging only. Interfacing owns rendering and fallback; Bridging owns adaptation; Searching owns search execution and contracts. Search backend, index lifecycle, reindexing, query logs, indexed-resource ledgers, and provider internals remain outside Interfacing's dependency surface.

