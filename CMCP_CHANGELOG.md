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
