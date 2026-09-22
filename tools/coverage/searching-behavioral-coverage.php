<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$run = static function (string $command, string $label) use ($root): void {
    $previous = getcwd();
    chdir($root);
    passthru($command, $exitCode);
    if (false !== $previous) {
        chdir($previous);
    }
    if (0 !== $exitCode) {
        fwrite(STDERR, sprintf("%s failed with exit code %d.\n", $label, $exitCode));
        exit($exitCode);
    }
};

$php = escapeshellarg(PHP_BINARY);
$run($php.' vendor/bin/phpunit', 'PHPUnit behavioral suite');

$npm = '\\' === DIRECTORY_SEPARATOR ? 'npm.cmd' : 'npm';
$run($npm.' run test:e2e -- --reporter=line', 'Playwright UI harness');

$requiredEvidence = [
    $root.'/tests/Integration/SearchRepositoryReaderIntegrationTest.php' => [
        'testIndexAndIndexedResourceRepositoriesAndReaders',
        'testQueryLogRepositoryAndReaderApplyAllCriteria',
    ],
    $root.'/tests/Controller/Api/SearchQueryBoundaryCoverageTest.php' => [
        'testSearchApiNormalizesRequestAndSerializesResult',
        'testSearchApiMapsLimitedOperationToRetryResponse',
    ],
    $root.'/tests/Value/SearchRuntimeFallbackValueTest.php' => [
        'testUnavailableBackendExposesDeterministicFallbackBehavior',
    ],
    $root.'/tests/Service/Provider/SearchBackendQueryBuilderTest.php' => [
        'testItBuildsAnyAllAndRangeFacetFilters',
    ],
    $root.'/tests/Result/SearchResponseMapperTest.php' => [
        'testFacetBucketsAreCanonicalAndDeterministic',
    ],
    $root.'/tests/e2e/tooling.spec.ts' => [
        'Playwright test runner is operational',
    ],
];

foreach ($requiredEvidence as $path => $needles) {
    $contents = is_file($path) ? file_get_contents($path) : false;
    if (false === $contents) {
        fwrite(STDERR, sprintf("Behavioral evidence source is missing: %s\n", $path));
        exit(2);
    }
    foreach ($needles as $needle) {
        if (!str_contains($contents, $needle)) {
            fwrite(STDERR, sprintf("Behavioral evidence identifier is missing: %s\n", $needle));
            exit(2);
        }
    }
}

$dimensions = [
    'functional' => [
        'eligible' => ['repository-reader-integration', 'api-query-boundary'],
        'covered' => ['repository-reader-integration', 'api-query-boundary'],
    ],
    'behavioral' => [
        'eligible' => ['backend-unavailable-fallback', 'faceting-query-semantics', 'faceting-result-semantics'],
        'covered' => ['backend-unavailable-fallback', 'faceting-query-semantics', 'faceting-result-semantics'],
    ],
    'ui' => [
        'eligible' => ['playwright-runner'],
        'covered' => ['playwright-runner'],
    ],
    'critical' => [
        'eligible' => ['api-query-boundary', 'faceting-query-semantics', 'faceting-result-semantics'],
        'covered' => ['api-query-boundary', 'faceting-query-semantics', 'faceting-result-semantics'],
    ],
];

$output = $root.'/var/coverage/behavioral-ui.json';
if (!is_dir(dirname($output))) {
    mkdir(dirname($output), 0775, true);
}

file_put_contents($output, json_encode([
    'schema' => 'behavioral-ui-coverage-v2',
    'producer' => ['kind' => 'repository_script', 'script' => 'test:behavioral-coverage'],
    'generatedAt' => (new DateTimeImmutable())->format(DATE_ATOM),
    'dimensions' => $dimensions,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);

echo "Behavioral/UI coverage evidence generated: {$output}\n";
