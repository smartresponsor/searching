# Interfacing Bridge Outbound Contract

Searching exposes `SearchInterfacingBridgeSurfaceContract` for Bridging/Interfacing integration.

Ownership:

```text
Searching   owns SearchSurface/SearchBridge runtime payloads.
Bridging    owns adaptation to Interfacing's consumer service.
Interfacing owns shell, top search, result page and provider-control rendering.
```

The Interfacing-facing route hints are `/interfacing/search`, `/interfacing/search/suggest`, and `/interfacing/search/config`. Searching's own `/api/search/surface*` endpoints remain runtime/provider surfaces and must not be consumed directly by Interfacing UI.
