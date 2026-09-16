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

## Iteration 6 — 2026-09-14 package-contract RC hardening

Current-tree reconnaissance revalidated `Searching` from clean `master` at `135edadd6154344f9e620c2b28e7cb82b1cfaef0` before mutation. The local quality baseline passed: PHP-CS-Fixer found 0 fixable files, PHPStan analyzed 294/294 files with 0 errors, and PHPUnit passed 66 tests / 264 assertions.

Read-and-comply contour for this run:

- `Searching`: `AGENTS.md`, `README.md`, Composer/test/quality configuration, all repository Markdown documentation, source/API/integration references, tests/gates, and the local `.gating/` rule runtime.
- `Objecting`: `AGENTS.md`, `README.md`, `composer.json`, `MANIFEST.json`.
- `Cruding`: `AGENTS.md`, `README.md`, `composer.json`, `MANIFEST.json`; its local runtime closure includes Collectioning and Tabling.
- `Viewing`: `AGENTS.md`, `README.md`, `composer.json`, `MANIFEST.json`; its local runtime closure includes Interfacing.
- `Interfacing`: `AGENTS.md`, `README.md`, `composer.json`; no `MANIFEST.json` exists in the current tree.
- `Collectioning` and `Tabling`: current README/Composer contracts were read to resolve the local first-party repository closure.
- `Gating`: `AGENTS.md`, `README.md`, `composer.json`, `MANIFEST.json`, plus the materialized local rule registry/mirrors.
- `Canonization`: root contract plus normative `Canon006`, `Canon008`, `Canon018`, `Canon019`, `Canon024`, `Canon029`, `Canon033`, `Canon034`, `Canon038`, `Canon043`, and `Canon045` textual rule documents.

Target-to-canon mapping and decisions:

- Canon006/018/019/029/034/038 remain satisfied by the existing role-first source topology, `searching/search` → `App\\Searching\\` / `Search*` identity, quality tooling, ignore baseline, and `search_*` component YAML names.
- Canon008 is preserved: no new foreign PHP imports were introduced merely to justify package dependencies.
- The orchestration-required application contour is now explicit in Composer: `objecting/object`, `cruding/crud`, `viewing/view`, and `interfacing/interface` are direct runtime dependencies.
- Canon043 is materialized for local development: first-party path-linked packages use exact `dev-master`, `symlink: true`, and pinned `options.versions`, with `minimum-stability: dev` / `prefer-stable: true`.
- Canon045 is materialized without inventing direct coupling: Collectioning and Tabling are exposed as root path repositories because they are reachable runtime dependencies of Cruding/Tabling, but they are not added as direct Searching requirements solely for repository discovery.
- Canon024/033 are materialized through a new path-independent `composer.prod.json` with package/type/PSR-4/PHP/Symfony identity parity and no local `path` repository declarations.
- `App\\Searching\\SearchingBundle` is explicitly published in Composer bundle metadata.

Selected RC-critical workstream:

- Make the application dependency graph, local path-repository closure, production package manifest, bundle entrypoint metadata, and lock/install state reproducible and self-validating.
- Add production-manifest validation to `check:searching` so packaging drift becomes a normal repository gate.

Separate post-RC growth workstream:

- Search quality/SLO telemetry, query analytics, relevance experiments/A-B testing, feedback-driven tuning, personalization, and hybrid/vector retrieval remain growth work. They do not expand this RC because the existing integration seal intentionally keeps Searching focused on search runtime/contracts while host adaptation/rendering stay outside this repository.

Material risks and safeguards:

- Adding the mandated application dependencies expands the installed dependency closure; Composer update therefore re-resolved compatible Symfony/Doctrine packages. The resulting lock graph must pass all existing Searching static/runtime tests before integration.
- Collectioning/Tabling are repository-visibility dependencies, not newly invented Searching runtime responsibilities.
- No Searching source, controllers, routes, Entity behavior, UI rendering, generic CRUD ownership, or navigation ownership is changed by this package-contract wave.

Verification completed so far:

- `composer update objecting/object cruding/crud viewing/view interfacing/interface collectioning/collection tabling/table --with-all-dependencies`: passed; local first-party packages are junctioned from sibling repositories and the lock file was refreshed.
- `composer validate --strict --check-lock`: passed.
- `composer validate --strict --no-check-all composer.prod.json`: passed.
- `composer check:searching`: passed; production-manifest validation, PHP-CS-Fixer, PHPStan 294/294, and PHPUnit 66/66 (264 assertions) are green.
- `composer audit`: passed with no known security vulnerability advisories.

Remaining acceptance: inspect the final diff/dependency graph, complete any available canonical gate evidence, commit the coherent package-contract change on the feature branch, push, and integrate through the protected-branch workflow when available.

## Iteration 7 — dual-runtime, persistence, test-tooling, and final RC acceptance

The package-contract pass exposed additional canonical runtime obligations once `Searching` was evaluated as a Symfony component rather than only a reusable package. Canon022/025/032/037/039/041 and the Doctrine persistence rules were therefore read and applied directly from Canonization before extending the patch.

Material implementation and fixes:

- Added minimal standalone Symfony runtime surfaces: `bin/console`, `App\\Searching\\Kernel`, and `config/bundles.php`, registering the existing `SearchingBundle` without introducing Host/UI ownership.
- Completed the standalone platform dependency baseline with direct Collectioning, Tabling, and EasyAdmin dependencies and an explicit FrameworkBundle dependency.
- Repaired a latent PSR-4 defect by renaming `src/Contract/Bridge/InterfacingSearchBridgeProviderInterface.php` to the file matching its declared `SearchInterfacingBridgeProviderInterface` FQCN.
- Added local standalone Framework/Doctrine configuration and ignored generated `config/reference.php` per Canon037; PHP-CS-Fixer no longer treats that disposable generated artifact as authored source.
- Added Doctrine Migrations runtime/configuration and generated the initial migration from current Doctrine metadata using `doctrine:migrations:diff --from-empty-schema`.
- Migration generation exposed a real schema defect: `idx_search_reindex_job_created_at` referenced `created_at` while the Entity metadata did not explicitly map that column name. `SearchReindexJobEntity` now maps `created_at` / `updated_at` explicitly.
- Added repository-owned `schema:parity` execution contract (`doctrine:schema:validate` plus `doctrine:migrations:up-to-date`).
- Added Canon039/041 tooling: explicit `src/` PHPUnit coverage population, persistent path/branch coverage summary script, Symfony Test Pack, Panther, repository-local Playwright dependency/configuration, lockfile, and executable Playwright runner smoke.
- Corrected the PHPUnit 11 coverage CLI contract from unsupported `--branch-coverage` to supported `--path-coverage`.
- Updated the final bridge integration seal and executable guard from stale `Service/Bridge` / legacy Contract paths to the actual `Provider/Bridge` and `Value/Bridge` topology, exposed it as `bridge:seal`, and included it in the aggregate Searching gate.
- `composer.lock` is now repository material for reproducible dependency state; Canon037 generated reference output remains ignored.

Persistence acceptance:

- Guarded Doctrine migration dry-run: 1 migration / 19 SQL queries, then the matching fingerprint was explicitly confirmed and applied to the `test` SQLite database.
- Post-apply migration plan: already at `App\\Searching\\Migration\\Version20260914091219`.
- `doctrine:schema:validate --env=test`: mapping correct and database schema in sync.
- `doctrine:migrations:up-to-date --env=test`: no migrations to execute.
- `composer schema:parity`: passed end-to-end.

Final quality/security evidence:

- `composer validate --strict --check-lock`: passed.
- `composer validate --strict --no-check-all composer.prod.json`: passed.
- `composer bridge:seal`: passed.
- PHP syntax lint: 7/7 changed/untracked PHP files passed.
- PHP-CS-Fixer: 296 files, 0 fixable files.
- PHPStan: 295/295 files, 0 errors.
- PHPUnit: 66/66 tests, 264 assertions passed on PHPUnit 11.5.56.
- Standalone Symfony boot: passed on Symfony 8.1.6 / PHP 8.4.13.
- `npm ci`: passed from the frozen lockfile; Playwright smoke: 1/1 passed.
- Composer audit: no known security advisories; npm audit: 0 vulnerabilities.
- The aggregate `check:searching` reached and passed production-manifest validation, standalone boot, bridge seal, CS, and PHPStan; the console wrapper terminated at its external ~33-second limit as PHPUnit started. PHPUnit was then run independently and passed, so this is recorded as an orchestration timeout rather than a product gate failure.

Coverage evidence / non-blocking debt:

- Canon040 evidence is valid and intentionally not manipulated: Lines 34.99% (1552/4436), Methods 38.43% (274/713), Branches 66.30% (789/1190), Paths 3.33% (352/10579).
- Lines and Methods classify as `HIGH_TEST_DEBT`; Branches are below the 70% target but above the high-debt threshold. Canon040 defines this as remediation/debt evidence rather than a hard execution failure. No source exclusions, baselines, or weakened thresholds were introduced to manufacture a pass.
- Canon042 behavioral/UI coverage evidence is not fabricated: Playwright tooling is operational, but no opaque percentage or fake workflow denominator is introduced. Behavioral inventory/evidence remains a post-RC measurable test-growth workstream.

RC decision before Git integration:

- Runtime, package, standalone, persistence, bridge, static-analysis, unit-test, package-security, and tooling contracts are green.
- Remaining known debt is explicit test-coverage maturity work, not a correctness/package/persistence blocker under Canon040/042 semantics.
- Functional RC integration completed through signed commit `86ba77e`, GitHub PR #3, and a squash merge to `origin/master` at `729c1c547a433359f6906e4cfa9983edba5d4359`.
- The feature branch remained clean and exactly synchronized with its pushed upstream after merge.
- Console MCP intentionally blocks switching directly to protected local `master`; this safety boundary was not bypassed. `origin/master` was fetched and verified as the authoritative merged state.

## Iteration 8 — final journal integration tail

This follow-up is documentation-only and exists solely to make the orchestration journal factual after PR #3 merged. No product/runtime/package/schema/test configuration is changed by this iteration.

Final integration facts before this journal-only follow-up:

- GitHub repository: `smartresponsor/searching`.
- Functional RC PR: #3 (`Harden Searching RC package and standalone contracts`).
- PR #3 state: merged with no merge-safety blockers; inspected head SHA matched `86ba77e974531e27c86d6243005ee74e5e827d39`.
- `origin/master` advanced from `135edadd6154344f9e620c2b28e7cb82b1cfaef0` to squash commit `729c1c547a433359f6906e4cfa9983edba5d4359`.
- The pre-merge feature worktree was clean, ahead/behind 0 against its upstream, and all accepted RC evidence remained green.
- Known residual debt remains the explicitly measured Canon040/042 testing-maturity work described above; no unrecorded correctness or integration blocker remains.

This Iteration 8 journal-only change is the final integration tail and should be merged without reopening the completed functional RC scope.

## Iteration 9 — post-RC coverage debt wave 1

This post-RC growth/debt wave changes tests only. Production source, runtime behavior, package contracts, schema, routes, controllers, and integration boundaries remain unchanged.

Target selection came directly from the Canon040 coverage report rather than arbitrary test expansion. The first wave focused on deterministic low-covered normalization/fallback contracts with high branch density and low test cost: `SearchProviderConfiguration`, `SearchIndexCriteria`, `SearchReindexJobCriteria`, `SearchRelevanceProfileCriteria`, `SearchSynonymCriteria`, `SearchUnavailableBackendClient`, `SearchBulkOperation`, and `SearchOperationLimitRequest`.

Material test work:

- Added `SearchCriteriaValueTest` covering aliases, trimming, invalid scalar/object input, boolean normalization, pagination bounds/defaults, date parsing, provider defaults, and backend configuration serialization.
- Added `SearchRuntimeFallbackValueTest` covering unavailable-backend no-op/index/search/status behavior, generator consumption, bulk operation validation/serialization, operation-limit identities/costs/metadata, global vs targeted reindex cost, and invalid-cost rejection.
- No production class was edited to make tests easier or to inflate coverage.

Verification and measured effect:

- Changed PHP lint: 2/2 test files passed.
- PHPUnit: 74/74 tests, 346 assertions passed (baseline 66 tests / 264 assertions before coverage work).
- Coverage script passed with Xdebug path coverage.
- Canon040 summary moved from Classes 25.13%, Methods 38.43%, Paths 3.33%, Branches 66.30%, Lines 34.99% to Classes 26.13%, Methods 40.25%, Paths 3.70%, Branches 75.29%, Lines 36.83%.
- Branch coverage therefore crossed the 70% maturity target without exclusions or denominator manipulation.
- `SearchProviderConfiguration` and `SearchUnavailableBackendClient` reached 100% Methods/Branches/Lines; `SearchOperationLimitRequest` reached 100% Lines and 90% Branches.

Remaining debt after wave 1:

- Lines and Methods remain the dominant Canon040 debt dimensions. The next efficient targets are orchestration/service classes with low method/line coverage (`SearchDocumentIndexer`, `SearchIndexLifecycleManager`, `SearchReindexCoordinator`, selected serializers/result mappers) rather than further path-explosion chasing in already high-line-covered builders.
- Canon042 behavioral workflow inventory remains a separate post-RC growth task and is not conflated with unit coverage.

## Iteration 10 — post-RC coverage debt wave 2

Wave 2 remains test-only. No production PHP, dependency, configuration, schema, route, controller, or integration contract is modified.

Coverage targets were selected from the post-wave-1 report by prioritizing orchestration/service classes with material uncovered lines and methods rather than chasing combinatorial path counts in already well-covered builders.

Material test work:

- Extended `SearchIndexLifecycleManagerTest` with lifecycle-provider delete, non-lifecycle fallback delete/index naming, and all-provider ensure orchestration.
- Added `SearchDocumentIndexerFailureTest` covering mixed current/changed bulk indexing, single-index failure bookkeeping/rethrow, and bulk failure bookkeeping for all pending documents.
- Added `SearchReindexCoordinatorCoverageTest` covering explicit reindex-request job creation, provider-level failure aggregation, and successful existing-job document processing/completion.
- Production code remained unchanged throughout the wave.

Verification and measured effect:

- Changed PHP lint: 3/3 files passed.
- PHP-CS-Fixer normalized new-file line endings and reported no semantic source changes.
- PHPStan: 299/299 files, 0 errors.
- PHPUnit: 83/83 tests, 410 assertions passed.
- Xdebug path-coverage run: passed.
- Coverage after wave 2: Classes 26.63% (53/199), Methods 40.81% (291/713), Paths 3.76% (398/10579), Branches 77.82% (926/1190), Lines 37.87% (1680/4436).
- Relative to the original RC baseline, Lines rose from 34.99% to 37.87%, Methods from 38.43% to 40.81%, and Branches from 66.30% to 77.82%.
- `SearchIndexLifecycleManager` reached 100% Methods / 100% Branches / 100% Lines.
- `SearchDocumentIndexer` reached 66.67% Methods / 96.43% Branches / 96.43% Lines.
- `SearchReindexCoordinator` improved to 75% Methods / 73.68% Branches / 85.71% Lines.

Wave-2 checkpoint:

- The highest-value deterministic service gaps selected for this wave are now covered.
- Remaining Canon040 debt is predominantly broad Methods/Lines distribution across serializers, null trackers, DTO/helper methods, and larger query/mapper surfaces. Further gains should be handled as a separate wave rather than expanding this focused change indefinitely.
- Path coverage remains intentionally non-targeted where high cyclomatic/path counts would incentivize low-value combinatorial tests.

## Iteration 11 — 2026-09-15 post-RC coverage debt wave 3 baseline

Reconnaissance started from the merged wave-2 state. GitHub PR #6 (`Raise Searching service coverage`) is merged; local `master` was safely realigned to `origin/master` at `84225890118a12b8ae2f146414b16883620de36e`, and this wave runs on the dedicated branch `cmcp/searching-coverage-wave3-20260915`.

Read-and-comply contour for this wave:

- `Searching`: root instructions/manifests/quality configuration, repository Markdown documentation, architecture/integration contracts, current coverage evidence, relevant source/test implementations, and package/runtime boundaries.
- `Objecting`, `Cruding`, `Viewing`, and `Interfacing`: current `AGENTS.md`, `README.md`, Composer manifests, and available repository manifests; no sibling repository is modified.
- `Canonization`: normative Canon006 plus Canon040 and Canon042 textual rules, and the current guard matrix.
- `Gating`: owner contract plus executable Canon040/Canon042 rule implementations, confirming that executable PHP coverage and behavioral/UI inventory evidence are separate warning/debt dimensions.

Target-to-canon mapping:

- Canon040 applies to `src/`. Current persistent evidence is Classes 26.63% (53/199), Methods 40.81% (291/713), Branches 77.82% (926/1190), Lines 37.87% (1680/4436). Branches exceed the 70% canonical target; Methods and Lines remain below both the 80% target and the 50% `HIGH_TEST_DEBT` threshold.
- Canon042 remains a separate growth/test-evidence track. No opaque denominator, route-count proxy, or fabricated percentage will be introduced in this PHP coverage wave.
- Canon006 and the component ownership seals are preserved because this wave changes tests/journal only; no production type/tree/runtime boundary is altered.
- Objecting/Cruding/Viewing/Interfacing responsibilities remain unchanged: no system-field clone, generic CRUD ownership, rendering responsibility, or Interfacing shell behavior is introduced into Searching.

RC-critical/test-debt work selected:

- Cover deterministic serializer helper/list methods that are currently executable but unexercised.
- Cover null tracker state transitions and compact indexing/health value semantics with stable assertions.
- Prefer direct behavioral assertions over path-combination chasing; production code will not be edited to manufacture coverage.

Separate growth workstream:

- Real backend relevance evaluation, hybrid/vector retrieval, query/click analytics, experimentation/A-B testing, personalization, and a reproducible Canon042 behavioral/UI inventory remain post-RC growth. These capabilities must not expand this bounded coverage-remediation wave.

Material risks and safeguards:

- Null reindex job keys are intentionally random; tests may assert shape/uniqueness properties, never a fixed key.
- Runtime timestamps are asserted semantically (presence/status/serialized format), not against wall-clock literals unless the source timestamp is explicitly supplied.
- Coverage gains are accepted only from real PHPUnit/php-code-coverage counters; test counts and path coverage are not used as substitute success metrics.

Planned gates:

- changed PHP syntax lint;
- PHP-CS-Fixer;
- PHPStan;
- PHPUnit;
- Xdebug persistent coverage (`composer test:coverage`);
- canonical/Gating evidence review plus aggregate repository checks;
- final Git diff/status, signed commit, push, PR integration, and post-merge state verification when green.

Wave-3 verification result:

- PHPUnit: 89 tests, 445 assertions, green.
- PHP-CS-Fixer: 301 files, clean after normalizing the new test file line ending.
- PHPStan: 300 files, zero errors.
- `check:searching`: production Composer manifest valid; Symfony standalone boot green; final search bridge seal PASS; style/static/tests green.
- `schema:parity`: Doctrine mapping valid, test database schema in sync, migrations up to date.
- Xdebug/php-code-coverage: Classes 32.16% (64/199), Methods 43.76% (312/713), Paths 3.99% (422/10579), Branches 81.01% (964/1190), Lines 39.16% (1737/4436).
- Delta from wave 2: +11 covered classes, +21 covered methods, +38 covered branches, +57 covered lines. Branch coverage remains above Canon040 target; Methods/Lines materially improve but remain `HIGH_TEST_DEBT`, so this bounded wave does not claim Canon040 completion.
- Targeted results now at 100% methods/branches/lines: `SearchNullIndexedResourceTracker`, `SearchNullReindexJobTracker`, `SearchIndexSerializer`, `SearchReindexJobSerializer`, `SearchRelevanceProfileSerializer`, `SearchResultSerializer`, `SearchSynonymSerializer`, `SearchHealthIndicator`, `SearchDocumentFingerprint`, `SearchIndexLifecycleRegistrySyncResult`, and `SearchIndexedResourceState`.

## Iteration 12 — 2026-09-15 post-RC coverage debt wave 4 baseline

Wave 4 starts from merged `master` `4fcd1ae096616521427c62b0cacf6377a430301c` on `cmcp/searching-coverage-wave4-20260915`. The prior engine/browser handoff attempt was blocked by `INPUT_DRAFT_CONTENT_CHANGED`; this is an orchestration UI-state blocker only and does not affect the authoritative local repository or Console MCP mutation path.

Selected bounded test-debt targets:

- `SearchIndexedResourceSerializer`: cover list serialization plus removed/fresh/stale ledger branches.
- `SearchResponseSerializer`: cover item/facet/highlight/suggestion/capability helpers directly.
- `SearchResponseMapper`: cover suggestion route metadata normalization and raw provider/backend metadata stripping.
- Criteria value objects: extend edge-input coverage only where it exercises currently uncovered helper branches; no production normalization semantics are changed.

Boundary and canon posture remain unchanged from Iteration 11: this is tests/journal only, Canon040 evidence remains the measurable target, Canon042 remains separate, and no Objecting/Cruding/Viewing/Interfacing ownership moves into Searching.

Wave-4 verification result:

- PHPUnit: 95 tests, 487 assertions, green.
- PHP-CS-Fixer: 302 files, clean after formatter-owned line-ending normalization; line-ending-only touched files have no semantic Git diff.
- PHPStan: 301 files, zero errors.
- `check:searching`: production Composer manifest valid; Symfony standalone boot green; final search bridge seal PASS; style/static/tests green.
- `schema:parity`: Doctrine mapping valid, test database schema in sync, migrations up to date.
- Xdebug/php-code-coverage: Classes 33.17% (66/199), Methods 44.88% (320/713), Paths 4.21% (445/10580), Branches 83.38% (993/1191), Lines 40.10% (1779/4436).
- Delta from wave 3: +2 covered classes, +8 covered methods, +29 covered branches, +42 covered lines. Canon040 remains `HIGH_TEST_DEBT` because Methods and Lines are still below 50%; branch coverage remains comfortably above its 70% target.
- `SearchResponseMapper`, `SearchResponseSerializer`, and `SearchIndexedResourceSerializer` now report 100% methods and lines; the indexed-resource serializer also reports 100% branches. Criteria helper branch coverage improved materially without changing normalization behavior.
- Repository Code Memory scope discovery reports no declared `memory:scope:resolve` Composer script; the available graph plan resolves the active Searching project as the only implementation target and the workspace graph as navigation-only, so no fabricated graph mutation is claimed.

## Iteration 13 — 2026-09-15 post-RC coverage debt wave 5 baseline

Wave 5 starts from merged `master` `16b5f5196e2ec930cfc0d85ad34f3669a4cecfd4` on `cmcp/searching-coverage-wave5-20260915`.

Selected high-yield test-debt targets:

- `SearchResultPayloadFactory`: null/error and scalar-fallback branches around slot/stats payload construction.
- `SearchDocumentNormalizer` and `SearchDocumentFingerprintCalculator`: normalization semantics, duplicate collapse, nested associative stability, and nullable text branches.
- `SearchNullProvider`: complete no-op provider contract rather than search-only coverage.
- `SearchPermissionChecker`: allowed-user, legacy metadata aliases, missing permission, private fallback, and unrestricted branches.

No production behavior, routes, schema, dependency manifests, or cross-component boundaries are planned to change in this wave.

Wave-5 verification result:

- PHPUnit: 101 tests, 521 assertions, green.
- PHP-CS-Fixer: 302 files, clean after formatter-owned line-ending normalization.
- PHPStan: 301 files, zero errors.
- `check:searching`: production Composer manifest valid; Symfony standalone boot green; final search bridge seal PASS; style/static/tests green.
- `schema:parity`: Doctrine mapping valid, test database schema in sync, migrations up to date.
- Xdebug/php-code-coverage: Classes 34.67% (69/199), Methods 45.86% (327/713), Paths 4.32% (457/10580), Branches 85.64% (1020/1191), Lines 40.73% (1807/4436).
- Delta from wave 4: +3 covered classes, +7 covered methods, +27 covered branches, +28 covered lines.
- `SearchNullProvider`, `SearchDocumentFingerprintCalculator`, and `SearchResultPayload` now report 100% methods/branches/lines; `SearchResultPayloadFactory` reaches 100% lines and 88.24% branches; `SearchPermissionChecker` reaches 100% lines and 96.36% branches.
- `SearchDocumentNormalizer` now exercises both nullable and non-null normalization branches and reaches 75% branch coverage / 100% lines, while php-code-coverage still reports its single method as uncovered because full path coverage is not achieved; no test-count proxy is used to override that tool-owned method metric.
- Canon040 remains `HIGH_TEST_DEBT`: Methods 45.86% and Lines 40.73% are still below the 50% high-debt boundary even though Branches 85.64% exceeds the canonical 70% target.

## Iteration 14 — 2026-09-15 post-RC coverage debt wave 6 baseline

Wave 6 starts from merged `master` `c2ab78c57601138ebd43c56296a63e32bfe37150` on `cmcp/searching-coverage-wave6-20260915`.

Selected high-yield integration target:

- Doctrine repositories plus their thin read services for indexes, indexed resources, reindex jobs, relevance profiles, and synonyms.
- Exercise real SQLite-backed criteria, count, identity/find-one, and get-or-create behavior inside a rollback-only test transaction.
- Keep repository implementation unchanged; this wave validates the existing persistence boundary rather than mocking final repositories or weakening types.

No production behavior, schema, routes, dependency manifests, or cross-component ownership changes are planned.

Wave-6 verification result:

- PHPUnit: 114 tests, 616 assertions, green.
- PHP-CS-Fixer: 304 files, clean.
- PHPStan: 303 files, zero errors.
- `check:searching`: production Composer manifest valid; Symfony standalone boot green; final search bridge seal PASS; style/static/tests green.
- `schema:parity`: Doctrine mapping valid, test database schema in sync, migrations up to date.
- Xdebug/php-code-coverage: Classes 43.22% (86/199), Methods 56.52% (403/713), Paths 4.29% (566/13208), Branches 87.21% (1268/1454), Lines 50.16% (2225/4436).
- Delta from wave 5: +17 covered classes, +76 covered methods, +248 covered branches, +418 covered lines. The path denominator increased because real repository/query criteria paths are now exercised and counted; Canon040 does not use path coverage as a threshold.
- The executable Canon040 high-debt classification is cleared: Lines >=50%, Methods >=50%, Branches >=40%. This does not claim the canonical coverage targets are complete; the normative targets remain Lines 80%, Methods 80%, Branches 70%.
- Real rollback-only SQLite integration now covers `SearchIndexRepository`, `SearchIndexedResourceRepository`, `SearchQueryLogRepository`, `SearchReindexJobRepository`, `SearchRelevanceProfileRepository`, `SearchSynonymRepository` and their thin reader services. Query-log filtering exercises all public criteria dimensions used by the repository.
- API boundary coverage now validates JSON/status semantics for provider status, health, indexed-resource listing, and query-log listing. Public observability/lifecycle value contracts were extended with deterministic coverage.
- Production source, routes, schema, migrations, package dependencies, and cross-component ownership remain unchanged.

## Iteration 15 — 2026-09-15 post-RC coverage debt wave 7

Wave 7 starts from merged `master` `1e7cdc94c73943afc661310fd1a1b3878998caae` on `cmcp/searching-coverage-wave7-20260915`.

Selected high-yield runtime boundaries:

- `SearchHealthChecker` readiness semantics across healthy, degraded, and unhealthy states, including provider availability, registry/index readiness, lifecycle errors/deletion, indexed-resource freshness, and reindex backlog thresholds.
- HTTP/API boundary coverage for `SearchIndexApiController`, `SearchRelevanceProfileApiController`, and `SearchSynonymApiController`, including list/create/update/delete success and not-found/validation branches.
- No production behavior, routes, schemas, migrations, dependency manifests, or cross-component ownership changes.

Wave-7 verification result:

- PHPUnit: 118 tests, 648 assertions, green.
- PHP-CS-Fixer: 305 files, clean.
- PHPStan: 304 files, zero errors.
- `check:searching`: production Composer manifest valid; Symfony standalone boot green; final search bridge seal PASS; style/static/tests green.
- `schema:parity`: Doctrine mapping valid, test database schema in sync, migrations up to date.
- Xdebug/php-code-coverage: Classes 43.22% (86/199), Methods 59.75% (426/713), Paths 4.63% (619/13380), Branches 87.79% (1380/1572), Lines 55.07% (2443/4436).
- Delta from merged wave 6: +23 covered methods, +112 covered branches, +218 covered lines; class count unchanged because these runtime classes already existed in the report.
- `SearchHealthChecker` now reports 87.50% methods, 98.33% branches, and 99.22% lines.
- `SearchIndexApiController` reports 71.43% methods / 97.50% lines; relevance-profile and synonym API controllers each report 83.33% methods / 95.83% lines.
- Canon040 remains above all HIGH_TEST_DEBT thresholds, but the normative 80% Methods / 80% Lines targets are still open; Branches remains above the 70% target.

## Iteration 16 — 2026-09-15 post-RC coverage debt wave 8

Wave 8 starts from merged `master` `23d001b0c9d92609849a19ff807ae3028d0f8728` on `cmcp/searching-coverage-wave8-20260915`.

Selected orchestration/runtime coverage:

- `SearchQueryExecutor` limiter rejection and failure trace logging.
- Explicit no-hydration / no-permission-filter execution mode with execution-context propagation.
- Provider exception logging and rethrow semantics.
- `SearchReindexCoordinator::requestReindex()` job-key contract and partial provider-failure accounting without aborting subsequent providers.
- No production source, routes, schema, migrations, dependency manifests, or cross-component ownership changes.

Wave-8 verification result:

- PHPUnit: 123 tests, 681 assertions, green.
- PHP-CS-Fixer: 305 files, clean.
- PHPStan: 304 files, zero errors.
- `check:searching`: production Composer manifest valid; Symfony standalone boot green; final search bridge seal PASS; style/static/tests green.
- `schema:parity`: Doctrine mapping valid, test database schema in sync, migrations up to date.
- Xdebug/php-code-coverage: Classes 44.22% (88/199), Methods 60.03% (428/713), Paths 4.66% (623/13381), Branches 88.05% (1385/1573), Lines 56.27% (2496/4436).
- Delta from merged wave 7: +2 covered classes, +2 covered methods, +5 covered branches, +53 covered lines.
- `SearchQueryExecutor` is now 100% Methods, 100% Branches, and 100% Lines (133/133); path coverage is 77.78% and is not a Canon040 threshold.
- Canon040 HIGH_TEST_DEBT remains cleared. Normative 80% Methods / 80% Lines targets remain open; Branches remains above the 70% target.

## Iteration 17 — 2026-09-16 post-RC coverage debt wave 9 baseline

Wave 9 starts on clean branch `cmcp/searching-coverage-wave9-20260916` at `082923afe9ba475211ce43e38b346ad80127d396`.

Reconnaissance and contracts consulted for this wave:

- `Searching`: `AGENTS.md`, `README.md`, Composer production/development manifests, package/test/static-analysis configuration, route/config files, architecture and integration documentation, the current source/test boundary, and persistent coverage evidence.
- Mandatory application contour: current Objecting, Cruding, Viewing, and Interfacing owner contracts and Composer package surfaces. Searching keeps business search execution only; generic CRUD remains Cruding-owned and final rendering remains Viewing/Interfacing-owned.
- `Canonization`: normative Canon006, Canon040, and Canon042 textual rules plus the guard matrix. Canon040 independently requires Lines >=80%, Methods >=80%, Branches >=70%; Canon042 explicitly forbids treating test counts as behavioral/UI coverage.
- `Gating`: owner contract and executable Canon-linked posture were inspected as enforcement companions rather than substituted for textual Canonization rules.

Target-to-canon mapping:

- Canon006 remains unchanged because this wave changes tests/journal only; no production role/tree taxonomy is altered.
- Canon040 is the measurable debt target. Current persistent php-code-coverage evidence is Classes 44.22% (88/199), Methods 60.03% (428/713), Branches 88.05% (1385/1573), Lines 56.27% (2496/4436). Branches already exceed target; Methods and Lines remain below the independent 80% targets.
- Canon042 remains a separate behavioral/UI evidence track. No Playwright count, route count, opaque percentage, or invented denominator is used as a substitute.
- Objecting/Cruding/Viewing/Interfacing ownership is preserved; no entity field, generic CRUD route/controller, view rendering, shell, or navigation responsibility moves into Searching.

Selected RC-critical/test-debt workstream:

- Cover currently unexecuted Search API controller boundaries with deterministic contract tests, prioritizing lifecycle, reindex-job, resource-registry, and bridge endpoints before broader query/response controller expansion.
- Measure all gains only through PHPUnit/php-code-coverage and keep production behavior unchanged unless a test exposes a real defect.

Separate growth workstream:

- Hybrid/vector retrieval, relevance judgments/evaluation, reranking/LTR, personalization, and experimentation remain post-RC capability growth. They are not required to close this bounded coverage-hardening wave.

Material safeguards:

- No production mutation is planned merely to raise coverage.
- No visual/UI implementation changes are in scope, so browser screenshots are not required for this test-only wave.
- Deterministic gates: changed PHP syntax, PHP-CS-Fixer, PHPStan, PHPUnit, persistent Xdebug coverage, aggregate Searching checks, schema parity, and final Git state.

## Iteration 17 — 2026-09-16 post-RC coverage debt wave 9 baseline

Wave 9 starts from a clean `cmcp/searching-coverage-wave9-20260916` worktree. This bounded pass remains test/debt hardening only: production behavior, routes, schema, migrations, dependencies, provider ownership, and UI rendering are not being expanded.

Reconnaissance/read-and-comply contour:

- `Searching`: `AGENTS.md`, `README.md`, Composer production/development manifests, PHPUnit/Playwright configuration, repository search architecture and integration documents, current source/test implementations, and persistent php-code-coverage evidence.
- `Objecting`, `Cruding`, `Viewing`, and `Interfacing`: current owner instructions, READMEs, Composer manifests, and available manifests/contracts. Their ownership boundaries remain external to this test-only wave.
- `Canonization`: textual architecture canon including Canon040 executable PHP coverage and Canon042 behavioral/UI evidence; existing identity/topology/runtime rules remain unchanged by this test-only wave.
- `Gating`: owner contract and executable-canon role were inspected as the enforcement companion; it is reference tooling, not a newly invented Searching runtime dependency.
- Code Memory: no repository-declared scope script exists; Console MCP resolves the active Searching graph as the only implementation target and the umbrella workspace graph as navigation-only.

Current Canon040 evidence:

- Classes 44.22% (88/199)
- Methods 60.03% (428/713)
- Branches 88.05% (1385/1573)
- Lines 56.27% (2496/4436)
- Paths 4.66% (623/13381), informational only and not a Canon040 threshold.

Target-to-canon mapping:

- Canon040 is the material rule for this wave. Branch coverage already exceeds the 70% target; Methods and Lines remain below the independent 80% targets, so the wave must add real php-code-coverage evidence without test-count proxies, exclusions, or denominator manipulation.
- Canon042 is explicitly separate. No UI/runtime surface is changed and no behavioral/UI percentage will be fabricated from Playwright counts or routes.
- Symfony-oriented `App\\Searching\\` role topology, package identity, dual runtime, first-party dependency declarations, and the Cruding/Objecting/Viewing/Interfacing ownership boundaries are preserved because no production topology or package surface is changed.

Selected RC/test-debt workstream:

- Increase method/line coverage in deterministic query-tuning and backend-neutral query/suggestion shaping where the current report shows high executable line coverage but materially incomplete method coverage.
- Prioritize behaviorally meaningful fallback/normalization/filter/tuning cases over combinatorial path chasing.
- Keep production source unchanged unless a test exposes a factual defect.

Separate growth workstream (not RC-blocking):

- Hybrid/vector/semantic retrieval, relevance quality/SLO telemetry, click/query analytics, experimentation/A-B testing, personalization, and a reproducible Canon042 behavioral/UI inventory remain post-RC growth.

Material safeguards:

- Elasticsearch/OpenSearch remain infrastructure providers behind Searching contracts; tests must assert provider-neutral payload semantics rather than vendor client behavior.
- Interfacing rendering, Viewing response generation, generic CRUD, and Objecting system-field ownership remain outside this wave.
- No user-observable UI is changed, so browser screenshot evidence is not applicable to this test-only pass.

Planned gates:

- changed PHP syntax lint;
- PHP-CS-Fixer;
- PHPStan;
- PHPUnit;
- Xdebug persistent coverage (`composer test:coverage`);
- standalone/package/bridge aggregate checks and schema parity;
- final Git diff/status, commit/push/PR integration, and post-integration verification when green.

## Iteration 17 — 2026-09-16 post-RC coverage debt wave 9 baseline

Task: `engine-20260915231536-searching-3ced66`.

Current-tree baseline:

- Branch: `cmcp/searching-coverage-wave9-20260916` at `082923afe9ba475211ce43e38b346ad80127d396`.
- Reconnaissance found one pre-existing untracked file, `tests/Controller/Api/SearchReindexApiControllerCoverageTest.php`; it was inspected before any mutation and is preserved as the active wave-9 candidate rather than overwritten or attributed to this pass.
- Persistent Canon040 evidence before wave 9: Classes 44.22% (88/199), Methods 60.03% (428/713), Paths 4.66% (623/13381), Branches 88.05% (1385/1573), Lines 56.27% (2496/4436).
- Full PHPUnit with the candidate test already passes: 126 tests / 695 assertions.

Read-and-comply contour for this run:

- `Searching`: `AGENTS.md`, `README.md`, Composer production/development manifests, PHPUnit/PHPStan/PHP-CS-Fixer/Playwright configuration, current coverage evidence, orchestration journal, and the reindex API/controller test boundary.
- `Objecting`: current `AGENTS.md`, `README.md`, `composer.json`, `MANIFEST.json`; lifecycle/system-field ownership remains outside this test-only wave.
- `Cruding`: current `AGENTS.md`, `README.md`, `composer.json`, `MANIFEST.json`; generic CRUD routes/controllers remain Cruding-owned and are not introduced here.
- `Viewing`: current `AGENTS.md`, `README.md`, `composer.json`, `MANIFEST.json`; final rendering remains Viewing-owned.
- `Interfacing`: current `AGENTS.md`, `README.md`, `composer.json`; shell/template ownership remains Interfacing-owned and is not changed here.
- `Canonization`: root contract plus normative `Canon006OneDominantTechnicalRoleRule`, `Canon040PhpTestCoverageRule`, `Canon042BehavioralUiCoverageRule`, and the architecture guard matrix.
- `Gating`: root contract and executable `Canon040PhpTestCoverageRule.php` / `Canon042BehavioralUiCoverageRule.php` mirrors. Canon040 consumes php-code-coverage counters; Canon042 requires an explicit repository-owned behavioral/UI inventory and must not be approximated from test counts.

Target-to-canon mapping:

- Canon006: this wave adds/validates only a PHPUnit test under the existing controller-test tree; no production class role/name/tree is changed.
- Canon040: wave 9 targets real php-code-coverage improvement for an uncovered HTTP orchestration boundary. The canonical targets remain Lines >=80%, Methods >=80%, Branches >=70%; the existing repository is no longer `HIGH_TEST_DEBT`, but Methods/Lines remain below target.
- Canon042: no browser/mobile UI, form, navigation, or user-visible rendering behavior changes in this wave. Playwright/Panther capability remains intact, but no behavioral/UI percentage is fabricated from unit/controller test counts.
- Objecting/Cruding/Viewing/Interfacing boundaries remain unchanged; no system-field, generic CRUD, rendering, or shell responsibility moves into Searching.

Market / maturity split within Searching responsibility:

- Baseline mature-search expectations are robust query/search execution, suggestions, synonyms/curation, relevance controls, operational health, and search analytics/zero-result visibility. The current Searching architecture already has provider-neutral query/suggestion, synonym/relevance configuration, health, query logging, reindex lifecycle, and bridge contracts.
- RC-critical work remains correctness, deterministic HTTP/runtime semantics, lifecycle/flow-control safety, diagnostics, and measurable executable coverage.
- Separate growth work remains relevance experimentation/A-B evaluation, richer search analytics/feedback loops, personalization, hybrid/vector retrieval, and product-level search quality optimization. Those do not expand this bounded test-debt wave.

Selected RC-critical workstream:

- Validate and complete the existing `SearchReindexApiControllerCoverageTest` so the sync/queued reindex API boundary, operation-limit status mapping, `Retry-After`, execution-context propagation, payload alias/normalization, and empty/non-array JSON fallback are measured by real PHPUnit coverage.
- Keep production code unchanged unless verification exposes a factual defect.

Material risks and safeguards:

- The candidate test predates this pass in the current worktree; it is treated as user/repository state and is not discarded.
- Reindex coverage assertions must validate public HTTP semantics and coordinator/dispatcher calls rather than private implementation details.
- No runtime restart is justified by this test-only wave; existing standalone/container checks are sufficient unless a gate exposes a runtime issue.

Planned acceptance gates:

- PHPUnit and Xdebug/php-code-coverage (`composer test`, `composer test:coverage`);
- PHP-CS-Fixer and PHPStan;
- production manifest, standalone boot, bridge seal, and aggregate `check:searching`;
- Doctrine `schema:parity` because the repository exposes persistence, even though this wave is test-only;
- final Git diff/status/upstream inspection, coherent signed commit, push, and protected-branch integration when green.

## Iteration 17 — 2026-09-16 post-RC coverage debt wave 9 baseline

Wave 9 starts from clean `cmcp/searching-coverage-wave9-20260916` at `082923afe9ba475211ce43e38b346ad80127d396`.

Reconnaissance/read-and-comply contour:

- `Searching`: `AGENTS.md`, `README.md`, `composer.json`, `composer.prod.json`, PHPUnit/PHPStan/PHP-CS-Fixer configuration, package/Playwright configuration, search routes/config, bridge/integration/flow-control/messenger documentation, current coverage evidence, memory graph plan, and the accumulated CMCP journal.
- `Objecting`: current `AGENTS.md`, `README.md`, `composer.json`, `MANIFEST.json`; system-field and Vendor identity ownership remains outside this test-only wave.
- `Cruding`: current `AGENTS.md`, `README.md`, `composer.json`, `MANIFEST.json`; generic CRUD ownership remains outside Searching.
- `Viewing`: current `AGENTS.md`, `README.md`, `composer.json`, `MANIFEST.json`; final rendering remains Viewing-owned.
- `Interfacing`: current `AGENTS.md`, `README.md`, `composer.json`; no current `MANIFEST.json` exists. Shell/templates remain Interfacing-owned and Searching exposes only its sealed bridge/response contracts.
- `Canonization`: owner contract, guard matrix, and normative Canon040/Canon042 textual rules. Canon040 requires Lines >=80%, Methods >=80%, Branches >=70% independently; Canon042 is a separate explicit behavioral/UI inventory contract.
- `Gating`: owner contract plus executable Canon040/Canon042 mirrors and rule registry.

Dependency/package mapping:

- Development and production manifests both declare Objecting, Cruding, Collectioning, Tabling, Viewing, and Interfacing as runtime dependencies; development path repositories use `symlink: true` and pinned `dev-master` identities.
- `composer.prod.json` preserves `searching/search`, `App\\Searching\\`, and `App\\Searching\\SearchingBundle` without local path repositories.
- Memory graph planning resolves Searching as the sole read-write implementation target and the shared workspace graph as navigation-only.

Opening market/enterprise comparison:

- Baseline mature search expectations include typo-tolerant autocomplete, synonyms, facets/filtering, relevance controls, safe result projection, observability, and index lifecycle/readiness.
- Advanced maturity includes behavioral search analytics, relevance experiments/A-B testing, learning-to-rank/reranking, personalization, and hybrid/vector retrieval.
- Those advanced capabilities remain growth work; the v0.27 integration seal explicitly prevents speculative Searching-internal expansion before host/Interfacing proof.

Selected RC-critical workstream:

- Continue Canon040 remediation with deterministic tests around currently under-covered runtime orchestration/value boundaries that have meaningful failure/fallback semantics, prioritizing coverage gains without production-source changes or combinatorial path chasing.
- Re-run lint, CS, PHPStan, PHPUnit, persistent Xdebug coverage, package/standalone/bridge/schema gates, and Gating evidence.

Separate growth workstream:

- Canon042 inventory production, real-backend relevance evaluation, click/query analytics, A/B testing, LTR/reranking, personalization, and hybrid/vector retrieval remain post-RC growth and must not expand this coverage wave.

Baseline evidence:

- Classes 44.22% (88/199), Methods 60.03% (428/713), Paths 4.66% (623/13381), Branches 88.05% (1385/1573), Lines 56.27% (2496/4436).
- Branches already exceed the Canon040 target; Methods and Lines are the remaining normative debt dimensions.

Material risks/safeguards:

- No production behavior, route, schema, migration, dependency, navigation, UI/rendering, or cross-component ownership change is justified by this wave.
- Tests must assert observable runtime contracts, not implementation-only counters, and coverage improvement must come from php-code-coverage evidence only.
- Browser/visual evidence is not applicable unless this wave unexpectedly changes user-observable UI, which is not planned.


## Iteration 17 — 2026-09-16 post-RC coverage debt wave 9 baseline

Wave 9 runs on `cmcp/searching-coverage-wave9-20260916` from a clean worktree. The persistent php-code-coverage baseline currently reports Classes 44.22% (88/199), Methods 60.31% (430/713), Paths 4.75% (637/13421), Branches 87.96% (1424/1619), and Lines 57.37% (2545/4436).

Reconnaissance and read-and-comply contour for this wave:

- `Searching`: authoritative engine specification, `AGENTS.md`, `README.md`, development/production Composer manifests, PHPUnit/Playwright contracts, current coverage evidence, orchestration journal, and the current bridge/reindex/flow-control integration documentation.
- `Objecting`, `Cruding`, `Viewing`, and `Interfacing`: current `AGENTS.md`, `README.md`, Composer manifests, and available manifests were read as read-only dependency contracts. Searching continues to declare all four as direct application dependencies with local path/symlink wiring; Collectioning and Tabling remain explicit first-party dependencies in the complete local repository closure.
- `Gating`: owner contract plus executable `Canon040PhpTestCoverageRule` and `Canon042BehavioralUiCoverageRule` mirrors.
- `Canonization`: owner contract, guard matrix, and normative textual `Canon006OneDominantTechnicalRoleRule`, `Canon040PhpTestCoverageRule`, and `Canon042BehavioralUiCoverageRule`.

Target-to-canon mapping:

- Canon006 remains unchanged because this wave is test/journal-only and does not alter production class names, role roots, or source topology.
- Canon040 is the measurable debt target. Branches already exceed the canonical 70% target; Methods 60.31% and Lines 57.37% remain below their independent 80% targets. The wave must increase tool-owned executable counters rather than use test counts or path coverage as substitutes.
- Canon042 remains a separate application-surface evidence track. Searching has Playwright tooling, but no opaque route-count or test-count proxy will be invented for functional/behavioral/UI coverage.
- Objecting/Cruding/Viewing/Interfacing boundaries remain fixed: no system-field clone, generic CRUD ownership, final rendering responsibility, shell ownership, or backend-provider internals are moved across component boundaries.

Market and maturity contour:

- Mature search platforms treat search analytics/no-result/conversion instrumentation as a normal search-quality feedback capability, while modern engines also expose semantic/vector and hybrid retrieval. OpenSearch documents hybrid keyword/semantic ranking through search pipelines; Typesense documents native search analytics plus vector/hybrid search.
- Searching already covers the RC baseline around provider-neutral query/index contracts, autocomplete, synonyms/relevance tuning, permission filtering, source hydration, health/diagnostics, backpressure, and lifecycle/job observability.
- Growth remains separate: hybrid/vector retrieval, click/conversion analytics, experimentation/A-B testing, personalization, and richer relevance feedback are post-RC capabilities and must not block the current correctness/test-debt wave.

Selected RC-critical test-debt workstream:

- Prioritize runtime classes where php-code-coverage still reports materially low method coverage despite meaningful user/operational behavior, especially reindex HTTP dispatch semantics, in-memory flow-control decisions, and tuning/query-builder branches where a bounded test can execute currently uncounted methods without production changes.
- Preserve production source, routes, schema, migrations, package manifests, and cross-component responsibility boundaries unless a test exposes a real defect requiring an in-scope correction.

Material risks and safeguards:

- Do not chase combinatorial path coverage in query builders merely to inflate a non-canonical metric.
- Time-based limiter assertions must avoid fragile wall-clock equality; validate semantic decisions and retry bounds instead.
- UI/browser evidence is not required for a tests-only wave because no user-observable UI, navigation, form, or interaction source is changed.

Planned gates: changed PHP syntax lint, PHP-CS-Fixer, PHPStan, PHPUnit, persistent Xdebug coverage, `check:searching`, `schema:parity`, final Git diff/status, and branch/upstream integration evidence.

Wave-9 verification result:

- Aggregate `check:searching`: GREEN — production Composer manifest valid; Symfony 8.1.6 / PHP 8.4.13 standalone test kernel boots; final search bridge seal passes; PHP-CS-Fixer clean; PHPStan clean; PHPUnit 139 tests / 834 assertions.
- `schema:parity`: GREEN — Doctrine mapping correct, test database schema in sync, migrations up to date.
- Persistent Xdebug/php-code-coverage: Classes 47.74% (95/199), Methods 63.25% (451/713), Paths 5.07% (682/13458), Branches 89.79% (1495/1665), Lines 59.90% (2657/4436).

## Iteration 18 — 2026-09-16 post-RC coverage debt wave 10 baseline

Wave 10 starts from clean merged master `f0751df38b7b31cf3588a181525bd2a92c7c9a0e`. Canon040 evidence is Classes 47.74% (95/199), Methods 63.25% (451/713), Branches 89.79% (1495/1665), Lines 59.90% (2657/4436). Branches remain above the independent 70% target; Methods and Lines remain below 80%.

Selected work remains test-only and behaviorally bounded: target concrete Searching runtime boundaries absent from the persistent coverage report, prioritizing provider/request mapping, execution-context resolution, and null/fallback tuning semantics before any path-heavy builder expansion. Production behavior, routes, schema, migrations, dependencies, rendering, navigation, and cross-component ownership remain unchanged unless a factual defect is exposed.

Planned gates: changed PHP lint, PHP-CS-Fixer, PHPStan, PHPUnit, persistent Xdebug coverage, `check:searching`, `schema:parity`, and final Git/PR integration.

### Iteration 17 consolidated acceptance note

The repeated `Iteration 17` baseline sections above are concurrent orchestration records from parallel wave-9 workers. They are retained as raw execution evidence rather than rewritten destructively. This note is the canonical closure for wave 9: the merged implementation is test-only, production behavior and component boundaries remain unchanged, `check:searching` and `schema:parity` are green, PHPUnit passes 139 tests / 834 assertions, and the authoritative Canon040 evidence is Classes 47.74% (95/199), Methods 63.25% (451/713), Branches 89.79% (1495/1665), Lines 59.90% (2657/4436). Canon040 HIGH_TEST_DEBT remains cleared; Methods and Lines remain below the independent 80% maturity targets and continue as post-RC debt, while Canon042 remains a separate behavioral/UI evidence track.
- Delta from wave-8 baseline: +7 covered classes, +23 covered methods, +110 covered branches, +161 covered lines. Canon040 branch threshold remains satisfied; method/line coverage improved materially but remain below the independent 80% targets, so residual post-RC test debt remains factual and visible.
- No production PHP, routes, schema, migrations, Composer dependencies, navigation, templates, or user-observable UI were changed by this wave.

## Iteration 17 — 2026-09-16 post-RC coverage debt wave 9 baseline

Wave 9 starts from a clean worktree on `cmcp/searching-coverage-wave9-20260916` with persistent Canon040 evidence from wave 8: Classes 44.22% (88/199), Methods 60.03% (428/713), Paths 4.66% (623/13381), Branches 88.05% (1385/1573), Lines 56.27% (2496/4436).

Reconnaissance and contract contour consulted for this run:

- `Searching`: `AGENTS.md`, `README.md`, `composer.json`, current orchestration journal, persistent coverage evidence, and the source/tests around the selected coverage targets.
- `Objecting`: `AGENTS.md`, `README.md`, `composer.json`, `MANIFEST.json`; system-field and lifecycle ownership remains outside this test-only wave.
- `Cruding`: `AGENTS.md`, `README.md`, `composer.json`, `MANIFEST.json`; generic CRUD ownership remains Cruding-owned.
- `Viewing`: `AGENTS.md`, `README.md`, `composer.json`, `MANIFEST.json`; rendering remains Viewing-owned.
- `Interfacing`: `AGENTS.md`, `README.md`, `composer.json`; shell/template ownership remains Interfacing-owned.
- `Gating`: `AGENTS.md`, `README.md`, `composer.json`, `MANIFEST.json`; executable canon enforcement remains external to the Searching runtime package.
- `Canonization`: root contract, guard matrix, normative `Canon040PhpTestCoverageRule` and `Canon042BehavioralUiCoverageRule` textual rules.

Target-to-canon mapping:

- Canon040 applies directly to `src/`. Branches already exceed the 70% target; Methods and Lines remain below the independent 80% targets. This wave uses only PHPUnit/php-code-coverage-owned counters and does not substitute test counts or path coverage.
- Canon042 remains a separate behavioral/UI evidence track. No UI/runtime surface is changed in this wave, and no behavioral denominator or synthetic percentage is introduced.
- Existing Symfony-oriented `App\\Searching\\` topology and Objecting/Cruding/Viewing/Interfacing responsibility boundaries are unchanged because the selected work is tests plus orchestration journal only.

Selected RC-critical/test-debt workstream:

- Cover Searching-owned HTTP orchestration boundaries that remained absent from the Canon040 report: index lifecycle validation/provider modes, searchable-resource projection, reindex-job list/view/not-found behavior, and synchronous/queued/rate-limited reindex dispatch.
- Keep the wave test-only so existing API semantics are verified rather than changed for coverage.

Market/maturity check for the same responsibility boundary confirms the separation: mature search platforms expose observable search APIs, relevance diagnostics/evaluation, tuning/experimentation, indexing/retry lifecycle, and analytics; OpenSearch documents search Explain/relevance-workbench flows, Elasticsearch exposes ranking evaluation, and Algolia separates indexing, relevance configuration, UI, analytics, and experimentation. The RC-critical implication for this wave is reliable boundary behavior and lifecycle evidence, not importing those vendors' UI, infrastructure, or experimentation ownership into Searching.

Separate post-RC growth workstream remains unchanged: relevance experiments, hybrid/vector retrieval, click/query analytics, personalization, and reproducible Canon042 workflow/UI inventory evidence.

Material risk is intentionally low: no production PHP, routes, schema, migrations, dependencies, navigation, UI, or cross-component contract will be changed. Planned gates are changed-PHP lint, PHP-CS-Fixer, PHPStan, PHPUnit, persistent Xdebug coverage, aggregate `check:searching`, schema parity, and final Git/upstream integration inspection.

## Iteration 18 — 2026-09-16 Canon040 coverage debt wave 10

Reconnaissance starts from clean `master`/`origin/master` checkpoint `f0751df38b7b31cf3588a181525bd2a92c7c9a0e`; the implementation branch is `cmcp/searching-coverage-wave10-20260916` from that exact remote state. The commits after wave-9 implementation only consolidated journal/verification evidence; no newer production or test coverage wave was present.

Current persistent Canon040 baseline: Classes 47.74% (95/199), Methods 63.25% (451/713), Paths 5.07% (682/13458), Branches 89.79% (1495/1665), Lines 59.90% (2657/4436). Branches remain above the independent 70% target; Methods and Lines remain the RC-critical debt dimensions against the independent 80% thresholds.

Selected bounded wave: cover the currently zero-covered Symfony Console command boundary (`src/Command/*`) using public `CommandTester` behavior and contract mocks. This target is preferable to further combinatorial query-builder path chasing because it can add previously untouched classes, methods, and executable lines simultaneously while validating real operational CLI semantics. Canon006 and the sealed Searching responsibility boundary remain unchanged because no production topology or runtime behavior is being modified.

Target-to-canon mapping: Canon040 receives only php-code-coverage-owned executable evidence; Canon042 remains separate because no UI/navigation/form/user-flow source changes. Objecting/Cruding/Viewing/Interfacing responsibilities remain external and unchanged. No Growth work (hybrid/vector search, analytics experimentation, personalization) is included.

Material risks: Symfony Console invalid-input paths may be rejected by InputDefinition before command execution, so tests must assert public CLI behavior rather than force impossible private paths. Date parsing and option normalization will be tested deterministically. Concurrent changes will be re-read before broad mutation; no destructive reset is permitted.

Planned gates: targeted PHPUnit, full `composer test`, persistent `composer test:coverage`, `composer cs:check`, `composer stan`, `composer check:searching`, `composer schema:parity`, final diff/status, signed commit, push/PR integration, and final origin/master cleanliness verification.

Wave-10 acceptance result: Canon040 is compliant on all independent thresholds — Methods 80.08% (571/713), Branches 89.49% (2000/2235), Lines 93.82% (4162/4436); Classes 69.35% (138/199), Paths 4.66% (880/18867). Compared with the wave-9 baseline, this is +120 covered methods and +1505 covered lines while preserving the existing production architecture and semantics.

Verification is GREEN on the reconciled concurrent state: `composer test` 175 tests / 1124 assertions with zero warnings at the final clean warning check; `composer test:coverage` 175 tests with persistent path coverage; `composer cs:check` 0/316 fixable; `composer stan` 315/315 with no errors; `composer check:searching` GREEN including composer-prod validation, Symfony test boot, final search bridge seal guard, CS, PHPStan, and PHPUnit; `composer schema:parity` GREEN with correct Doctrine mapping, in-sync schema, and no pending migrations.

The wave remained test-only plus orchestration journal updates. No production PHP, routes, schema, migrations, dependencies, rendering, navigation, or cross-component ownership changed. Concurrent coverage work committed as `1cbe2f2` was preserved and reconciled rather than reset. Visual evidence remains NOT_VERIFIED because no UI/user-flow source changed; Canon042 remains a separate track and no evidence was fabricated.

## Iteration 19 — 2026-09-16 Canon040 completion and RC acceptance

Task: `engine-20260916152704-searching-1744e4`.

Current-tree reconnaissance preserved eight pre-existing untracked coverage-test groups on `cmcp/searching-coverage-wave10-20260916` rather than overwriting or discarding them. The branch began this execution window two commits ahead of `origin/master`; no production PHP, routes, schema, migrations, dependencies, templates, navigation, or user-observable UI changes were introduced by this pass.

Read-and-comply contour:

- `Searching`: root instructions, README, development/production Composer manifests, PHPUnit/PHPStan/PHP-CS-Fixer/Playwright configuration, search routes/config, messenger/flow-control/Interfacing integration documentation, current source/test boundary, persistent coverage evidence, and this orchestration journal.
- `Objecting`, `Cruding`, `Viewing`, and `Interfacing`: current `AGENTS.md`, `README.md`, Composer manifests, and available manifests. Interfacing has no current `MANIFEST.json`; its shell/rendering ownership remains external to Searching.
- `Canonization`: normative textual `Canon006OneDominantTechnicalRoleRule`, `Canon040PhpTestCoverageRule`, `Canon042BehavioralUiCoverageRule`, and the architecture guard matrix.
- `Gating`: owner contract and executable-canon role as enforcement companion; it is not substituted for textual Canonization rules.

Target-to-canon mapping:

- Canon006: this wave remains test/journal-only and therefore does not alter the dominant technical role, namespace, class/file topology, or package boundary of production code.
- Canon040: authoritative php-code-coverage thresholds are Lines >=80%, Methods >=80%, Branches >=70%, independently. Fresh evidence after the current test set is Classes 69.35% (138/199), Methods 80.08% (571/713), Paths 4.66% (880/18867), Branches 89.49% (2000/2235), Lines 93.82% (4162/4436). All three normative Canon040 thresholds are therefore satisfied without exclusions, denominator manipulation, or test-count proxies.
- Canon042: no browser/mobile UI, navigation, form, interaction, or user-flow source changed. Behavioral/UI inventory remains a separate growth/evidence track; screenshots are not applicable to this tests-only pass.
- Objecting/Cruding/Viewing/Interfacing responsibilities remain unchanged: no system-field ownership, generic CRUD mechanics, final rendering, or shell ownership moved into Searching.

Market / maturity split:

- Current official OpenSearch material treats hybrid keyword/semantic retrieval and relevance-workbench optimization as advanced search-quality capabilities; Elasticsearch exposes ranking evaluation over judged query sets. These reinforce a post-RC growth direction around measured relevance rather than changing the current bounded test-hardening objective.
- RC-critical work for this pass is deterministic coverage of existing query, command, controller, DI, persistence-support, bridge/provider, and runtime contracts plus static/runtime gates.
- Separate growth remains hybrid/vector retrieval, judged relevance evaluation, click/query analytics, experimentation/A-B testing, personalization, and Canon042 behavioral/UI inventory work. None is required to close Canon040.

Verification and repairs:

- Initial PHPUnit with the candidate tests passed 175 tests / 1113 assertions but reported one warning; concurrent/current-tree refinements were re-read rather than overwritten.
- PHP-CS-Fixer initially found formatting-only line-ending/EOF/import issues in the new tests; `composer cs:fix` normalized all eight test files and the follow-up check is clean.
- PHPStan initially exposed test-only typing debt; after current-tree refinements it is GREEN with 315/315 files and zero errors.
- Final PHPUnit: 175 tests / 1143 assertions, GREEN with no warnings.
- Fresh persistent Xdebug coverage: Classes 69.35%, Methods 80.08%, Branches 89.49%, Lines 93.82%; Canon040 GREEN.
- `composer check:searching`: GREEN — production manifest valid, Symfony 8.1.6 / PHP 8.4.13 standalone test kernel boots, final search bridge seal PASS, PHP-CS-Fixer clean, PHPStan clean, PHPUnit green.
- `composer schema:parity`: GREEN — Doctrine mapping correct, test database schema in sync, migrations up to date.
- Changed PHP syntax lint: 8/8 new test files GREEN.

Remaining acceptance tail: inspect final diff/status, stage the coherent test+journal change, commit/push under repository policy, integrate through the protected-branch workflow if available, then verify final HEAD/upstream/worktree state.

Wave-10 implementation and verification result:

- Added behaviorally meaningful coverage across Console commands, admin/API boundaries, DI/compiler wiring, bridge/provider contracts, persistence writers/dispatchers, runtime fallback/value contracts, and result payload construction; production source remained unchanged.
- PHPUnit: 175 tests / 1143 assertions, green with no warnings.
- PHPStan: 315 files, 0 errors. PHP-CS-Fixer: 316 files, 0 fixable files.
- `check:searching`: green; standalone Symfony boot, production manifest, bridge seal, PHPStan, style, and PHPUnit all pass.
- `schema:parity`: green; Doctrine mapping/schema/migrations are synchronized.
- Persistent Canon040 evidence: Classes 69.35% (138/199), Methods 80.08% (571/713), Branches 89.49% (2000/2235), Lines 93.82% (4162/4436), Paths 4.66% (880/18867, informational only).
- Canon040 is now compliant on all three independent normative thresholds: Methods >=80%, Lines >=80%, Branches >=70%. No coverage exclusions, production-semantic expansion, or path-coverage chasing were used.
