<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$sourceRoot = $root.'/src';

$humanize = static function (string $name): string {
    $value = preg_replace('/([a-z0-9])([A-Z])/', '$1 $2', $name) ?? $name;

    return strtolower(trim(str_replace('_', ' ', $value)));
};

$classDescription = static function (string $name) use ($humanize): string {
    return sprintf(
        'Defines the %s responsibility within the Searching component runtime and its typed boundaries.',
        $humanize($name),
    );
};

$methodDescription = static function (string $name) use ($humanize): string {
    $patterns = [
        'build' => 'Builds the %s used by the Searching component execution and integration boundaries.',
        'serialize' => 'Serializes the %s into the stable representation exposed by the Searching component.',
        'resolve' => 'Resolves the %s required by the Searching component execution flow.',
        'find' => 'Finds the %s through the Searching component read or persistence boundary.',
        'list' => 'Lists the %s exposed through the Searching component read boundary.',
        'count' => 'Counts the %s matching the supplied Searching component criteria.',
        'execute' => 'Executes the %s operation through the Searching component runtime boundary.',
        'create' => 'Creates the %s required by the Searching component runtime flow.',
        'remove' => 'Removes the %s through the Searching component mutation boundary.',
        'delete' => 'Deletes the %s through the Searching component mutation boundary.',
        'index' => 'Indexes the %s through the Searching component indexing boundary.',
        'search' => 'Searches the %s through the Searching component query boundary.',
        'suggest' => 'Builds suggestions for the %s through the Searching component query boundary.',
        'map' => 'Maps the %s into the stable Searching component boundary representation.',
        'normalize' => 'Normalizes the %s according to the Searching component boundary contract.',
        'load' => 'Loads the %s required by the Searching component runtime configuration.',
        'process' => 'Processes the %s through the Searching component runtime workflow.',
        'supports' => 'Determines whether the Searching component supports the requested %s contract.',
        'handle' => 'Handles the %s through the Searching component execution boundary.',
        'dispatch' => 'Dispatches the %s through the Searching component asynchronous boundary.',
        'register' => 'Registers the %s within the Searching component runtime registry.',
        'collect' => 'Collects the %s required by the Searching component runtime boundary.',
    ];

    foreach ($patterns as $prefix => $template) {
        if (str_starts_with(strtolower($name), $prefix)) {
            $subject = substr($name, strlen($prefix));
            $subject = '' === $subject ? $name : $subject;

            return sprintf($template, $humanize($subject));
        }
    }

    return sprintf(
        'Executes the %s responsibility defined by the Searching component contract.',
        $humanize($name),
    );
};

$isMethodEligible = static function (string $modifiers, string $name): bool {
    if (1 === preg_match('/\bprivate\b/', $modifiers) || str_starts_with($name, '__')) {
        return false;
    }

    return 1 !== preg_match('/^(?:get|set|is|has)[A-Z_]/', $name);
};

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($sourceRoot, FilesystemIterator::SKIP_DOTS),
);

$changed = 0;
foreach ($iterator as $file) {
    if (!$file instanceof SplFileInfo || 'php' !== strtolower($file->getExtension())) {
        continue;
    }

    $path = $file->getPathname();
    $contents = file_get_contents($path);
    if (false === $contents) {
        throw new RuntimeException(sprintf('Unable to read %s.', $path));
    }

    $insertions = [];

    if (preg_match_all(
        '/(?:(\/\*\*.*?\*\/)\s*)?(?:#\[[^\r\n]*\]\s*)*(?:final\s+|abstract\s+|readonly\s+)*(?:class|interface|trait|enum)\s+([A-Za-z_][A-Za-z0-9_]*)/s',
        $contents,
        $classes,
        PREG_SET_ORDER | PREG_OFFSET_CAPTURE,
    )) {
        foreach ($classes as $match) {
            if ('' !== trim($match[1][0])) {
                continue;
            }

            $insertions[] = [
                'offset' => $match[0][1],
                'text' => "/**\n * ".$classDescription($match[2][0])."\n */\n",
            ];
        }
    }

    if (preg_match_all(
        '/(?:(\/\*\*.*?\*\/)\s*)?(?:#\[[^\r\n]*\]\s*)*((?:(?:public|protected|private|static|final|abstract)\s+)*)function\s+&?\s*([A-Za-z_][A-Za-z0-9_]*)\s*\(/s',
        $contents,
        $methods,
        PREG_SET_ORDER | PREG_OFFSET_CAPTURE,
    )) {
        foreach ($methods as $match) {
            if ('' !== trim($match[1][0]) || !$isMethodEligible($match[2][0], $match[3][0])) {
                continue;
            }

            $insertions[] = [
                'offset' => $match[0][1],
                'text' => "/**\n * ".$methodDescription($match[3][0])."\n */\n",
            ];
        }
    }

    if ([] === $insertions) {
        continue;
    }

    usort($insertions, static fn (array $left, array $right): int => $right['offset'] <=> $left['offset']);
    foreach ($insertions as $insertion) {
        $contents = substr($contents, 0, $insertion['offset'])
            .$insertion['text']
            .substr($contents, $insertion['offset']);
    }

    file_put_contents($path, $contents);
    ++$changed;
}

echo sprintf("PHPDoc hardening updated %d source files.\n", $changed);
