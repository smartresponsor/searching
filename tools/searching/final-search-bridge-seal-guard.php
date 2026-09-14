<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$required = [
    'src/Contract/Bridge/SearchInterfacingBridgeProviderInterface.php',
    'src/Provider/Bridge/SearchInterfacingBridgeProvider.php',
    'src/Service/Bridge/SearchBridgeConfigSerializer.php',
    'src/Value/Bridge/SearchInterfacingBridgeDefinition.php',
    'docs/interfacing-bridging-contract.md',
    'docs/interfacing-bridge-adapter.md',
    'docs/interfacing-consumption.md',
    'docs/final-integration-seal-v0.27.md',
    'docs/final-integration-seal-v0.30.md',
];

$failures = [];
foreach ($required as $relative) {
    if (!is_file($root . '/' . $relative)) {
        $failures[] = sprintf('Missing required Searching bridge seal file: %s', $relative);
    }
}

$provider = $root . '/src/Provider/Bridge/SearchInterfacingBridgeProvider.php';
$interface = $root . '/src/Contract/Bridge/SearchInterfacingBridgeProviderInterface.php';
$extension = $root . '/src/DependencyInjection/SearchingExtension.php';

if (is_file($provider)) {
    $source = (string) file_get_contents($provider);
    foreach ([
        'SearchResponseProviderInterface',
        'SearchSuggestionResponseProviderInterface',
        'SearchBridgeConfig',
        'SearchBridgeAutocompleteConfig',
        'SearchBridgeResultPageConfig',
    ] as $needle) {
        if (!str_contains($source, $needle)) {
            $failures[] = sprintf('SearchInterfacingBridgeProvider missing expected bridge marker: %s', $needle);
        }
    }
}

if (is_file($interface)) {
    $source = (string) file_get_contents($interface);
    foreach (['getBridgeConfig', 'search', 'suggest'] as $method) {
        if (!str_contains($source, 'function ' . $method . '(')) {
            $failures[] = sprintf('SearchInterfacingBridgeProviderInterface missing method: %s', $method);
        }
    }
}

if (is_file($extension)) {
    $source = (string) file_get_contents($extension);
    if (!str_contains($source, 'SearchInterfacingBridgeProviderInterface::class') || !str_contains($source, 'SearchInterfacingBridgeProvider::class')) {
        $failures[] = 'SearchingExtension must alias SearchInterfacingBridgeProviderInterface to SearchInterfacingBridgeProvider.';
    }
}

if ($failures !== []) {
    fwrite(STDERR, "[final-search-bridge-seal-guard] FAIL\n" . implode("\n", $failures) . "\n");
    exit(1);
}

echo "[final-search-bridge-seal-guard] PASS\n";
