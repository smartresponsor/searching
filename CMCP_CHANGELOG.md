# CMCP RC Orchestration Journal

Task: `engine-20260911151642-searching-91a85f`
Component: `Searching`
Authoritative workspace: `D:\PhpstormProjects\www\Searching`

## Iteration 1 — Reconnaissance and baseline

Read and inspected the local repository state, Git branch/upstream, `README.md`, `AGENTS.md`, `composer.json`, PHPUnit/PHPStan/PHP-CS-Fixer configuration, component documentation, source integration boundaries, tests, and the current local `.gating/` support tree.

Mandatory sibling contour consulted read-only:

- `Objecting`: system-field, identity, lifecycle, versioning, and consumer ownership boundary.
- `Cruding`: generic CRUD routes/controllers remain Cruding-owned.
- `Viewing`: final rendering boundary remains Viewing-owned.
- `Interfacing`: UI/rendering contracts remain Interfacing-owned; the current renderable interface is `App\Interfacing\Contract\InterfaceSurfaceRenderableInterface`.
- `Gating`: executable enforcement companion and Canon-linked rule mirrors.
- `Canonization`: normative textual rules, including Canon006, Canon018, Canon019, Canon029, Canon034, and Canon038.

Canonical identity mapping:

- Composer: `searching/search`.
- Component token: `searching`.
- Namespace root: `App\Searching\ => src/`.
- Subject token: `search`.
- Component-owned PHP subject prefix: `Search*`.

Selected RC-critical work:

1. Remove the undeclared direct Interfacing type dependency from `SearchResultPayload`. Searching's own v0.30 integration seal assigns adaptation to Bridging and rendering to Interfacing; Searching does not declare `interfacing/interface` and the package is not installed locally.
2. Replace stale `phpunit.xml` copied from Accessing (`App\Accessing\Kernel`, Accessing suites, nonexistent `tests/bootstrap.php`) with the actual Searching test suite/bootstrap.
3. Satisfy Canon029 by declaring PHPStan and exposing reproducible PHP-CS-Fixer, PHPStan, and PHPUnit Composer scripts.

Material risks:

- Pre-existing local `.gating/` and `AGENTS.md` were untracked at task start. They are preserved and intentionally excluded only through local `.git/info/exclude` so they are neither deleted nor accidentally staged by this RC task.
- `config/packages/search_config.yaml` and similarly named component-owned YAML may conflict with Canon038's `search_*` filename prefix. Renaming requires a non-destructive move path and full caller/import verification; this is inspected during debt closure rather than deleted/recreated blindly.
- The protected `master` branch requires integration through a feature branch/PR after the local RC commit is clean.

Planned gates:

- `composer validate --strict`
- PHP syntax lint for changed PHP files
- PHP-CS-Fixer check
- PHPStan
- PHPUnit
- applicable Gating/Canon checks
- final Git/upstream/PR state inspection

## Iteration 2 — Material implementation

- Decoupled `SearchResultPayload` from an undeclared Interfacing interface while preserving its Searching-owned template/fallback methods used by `SearchResultPayloadBuilder`.
- Repaired the stale PHPUnit root configuration so the suite boots from `vendor/autoload.php` and discovers the actual `tests/` tree.
- Added PHPStan as a dev dependency and Composer scripts for formatter check/fix, static analysis, tests, and the aggregate Searching quality check.

## Iteration 3 — Verification and fix

Verification exposed and closed both runtime and quality-tooling defects rather than suppressing them:

- Repaired stale named arguments (`name` → `nameEntity`) in provider configuration/status paths.
- Fixed readonly-array access in `SearchIndexNameBuilder`.
- Corrected indexed-resource entity imports and several stale test constructor call sites.
- Hardened controller payload normalization, criteria parsing, Doctrine repository generics, serializer metadata shapes, provider status normalization, and operation-limit/result DTO typing for PHPStan max level.
- Removed nonexistent PHPStan stub references; no baseline, ignore expansion, or level reduction was introduced.
- Fixed PHP-CS-Fixer/PHPStan incompatibility by disabling `phpdoc_to_comment`, preserving inline `@var` shape assertions.
- `composer validate --strict`: passed.
- Changed PHP syntax lint: passed.
- `composer check:searching`: passed with PHP-CS-Fixer 0 fixable files, PHPStan 294/294 with 0 errors, PHPUnit 66/66 with 264 assertions.

## Iteration 4 — Debt closure and integration

Architecture and canon debt closure:

- Removed the legacy Searching-owned `Surface` taxonomy. Search request/result/capability types now live under technical-role `Query`, `Result`, and `Provider` value trees; bridge integration values live under `Value/Bridge`.
- Replaced concrete `*Contract` classes with role-correct values/definitions. `Contract/` is now reserved for actual interfaces.
- Kept `_view.surface` unchanged because it is a Viewing-owned serialized schema field, not a Searching architecture root.
- Replaced `ServiceInterface/` as a generic interface bucket. Forty-one standalone polymorphic runtime interfaces now live under `src/Contract/<role>/`, consistent with Canon002's standalone-contract exception instead of inventing false one-to-one `*ServiceInterface` mirrors.
- Applied Canon006 dominant-role topology: first-class `Builder`, `Factory`, `Provider`, and `Resolver` classes were moved out of generic `Service/` into their technical-role roots.
- Applied Canon018 subject vocabulary: component-owned implementation names now use the `Search*` prefix, including null/Doctrine/messenger/backend variants.
- Applied canonical route grammar: compound route concepts use singular slash-separated segments and dynamic route placeholders use canonical `{id}`, `{slug}`, or `{token}` forms.
- Applied Canon038: component-owned YAML files are now `config/packages/search_config.yaml` and `config/routes/search_routes.yaml`.
- Applied Canon034: `.gitignore` now covers `.env.local` and `.env.*.local`.
- Strict Gating: 13 rules, 0 failed, 0 warnings; two expected skips (`mirror.service_interface` because the mirror tree is intentionally absent, and database prefix because no relevant table declaration was discovered by that profile pass).
- Canon-linked RC pass: Canon006, Canon018, Canon019, Canon029, Canon034, and Canon038 all passed with 0 failed / 0 warnings.
- Final aggregate quality after role moves: PHP-CS-Fixer clean, PHPStan 294/294 0 errors, PHPUnit 66 tests / 264 assertions.

Git integration is the remaining Iteration 4 step: create the coherent RC commit, move to a feature branch, push, open PR, merge when policy permits, and synchronize local master.

## Iteration 5 — Final acceptance and handoff

Pending post-integration Git acceptance: final HEAD/upstream/PR/merge/worktree state and deferred-growth handoff.
