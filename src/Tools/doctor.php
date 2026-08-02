#!/usr/bin/env php
<?php

/**
 * doctor.php — report modules the kernel cannot see
 *
 * Usage:
 *   php src/Tools/doctor.php                 # check every registered module path
 *   php src/Tools/doctor.php --quiet         # only print problems
 *   php src/Tools/doctor.php --root=/path    # explicit app root (auto-detected otherwise)
 *
 * Module discovery is a filesystem scan: InitModsImproved globs
 *
 *     Modules/{Name}/Routes/Routes.php
 *     Modules/{Parent}/Modules/{Name}/Routes/Routes.php
 *     Modules/{A}/Modules/{B}/Modules/{Name}/Routes/Routes.php
 *
 * Anything that misses those patterns is skipped in silence — no log line, no
 * warning — and its routes simply never exist. The symptom is a 404 that looks
 * exactly like a mistyped URL.
 *
 * This is deliberately a CLI check and NOT a runtime warning. Absence is not an
 * error: a module that does not exist has no routes, and a framework that warns
 * about things that are not there cries wolf. What this reports is narrower — a
 * Routes directory that exists, holds PHP, and was walked past anyway.
 *
 * Exit codes: 0 nothing to report, 1 problems found, 2 bad usage.
 * Run it when a route 404s and you cannot see why.
 *
 * @package upMVC
 * @license MIT
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("doctor.php is CLI only\n");
}

$args = array_slice($argv, 1);

foreach ($args as $arg) {
    if ($arg === '--quiet' || $arg === '--strict' || str_starts_with($arg, '--root=')) {
        continue;
    }
    if ($arg === '--help' || $arg === '-h') {
        printUsage();
        exit(0);
    }
    echo "[ERROR] Unknown option: $arg" . PHP_EOL . PHP_EOL;
    printUsage();
    exit(2);
}

$quiet   = in_array('--quiet', $args, true);
$strict  = in_array('--strict', $args, true);
$appRoot = resolveAppRoot($args);

if (!defined('UPMVC_APP_ROOT')) {
    define('UPMVC_APP_ROOT', $appRoot);
}

require_once $appRoot . '/vendor/autoload.php';

use App\Etc\Application;

// register() lets packages contribute their own module paths. boot() needs a
// Router and is deliberately not called.
$app = Application::getInstance();
$app->registerProviders();

$modulePaths = $app->getModulePathsForRoutes();
$ignorePatterns = $strict ? [] : loadIgnoreList($appRoot);
$problems    = [];
$ignored     = [];
$seenOk      = 0;

foreach ($modulePaths as $modulesPath) {
    $modulesPath = rtrim(str_replace('\\', '/', $modulesPath), '/');
    if (!is_dir($modulesPath)) {
        $problems[] = [
            'file'   => $modulesPath,
            'reason' => 'Registered module path does not exist on disk.',
            'fix'    => 'Remove the addModulePath() call, or create the directory.',
        ];
        continue;
    }

    $discovered = discoverFiles($modulesPath);

    foreach ($discovered as $file) {
        $class = classNameFor($modulesPath, $file);

        if (!class_exists($class)) {
            $problems[] = [
                'file'   => rel($appRoot, $file),
                'reason' => "Found by discovery, but class $class does not autoload.",
                'fix'    => 'The namespace must mirror the folder path. Declare exactly: namespace ' . substr($class, 0, strrpos($class, '\\')) . ';',
            ];
            continue;
        }

        if (!method_exists($class, 'Routes') && !method_exists($class, 'routes')) {
            $problems[] = [
                'file'   => rel($appRoot, $file),
                'reason' => "Class $class loads, but has no routes() method.",
                'fix'    => 'Add: public function routes($router) — not static, not addRoutes().',
            ];
            continue;
        }

        $seenOk++;
    }

    // Now the silent cases: PHP sitting in a Routes-ish folder that no glob
    // matched. Compared case-insensitively, because glob() is case-insensitive
    // on Windows and macOS — so this reflects what really happens here.
    $discoveredLower = array_map('strtolower', $discovered);

    foreach (routeishFiles($modulesPath) as $file) {
        if (in_array(strtolower($file), $discoveredLower, true)) {
            // Loads on this machine. It may still be a portability bug.
            if ($warning = casingWarning($file)) {
                $problems[] = [
                    'file'   => rel($appRoot, $file),
                    'reason' => $warning,
                    'fix'    => 'Rename it to the exact casing: Routes/Routes.php',
                ];
            }
            continue;
        }
        $problems[] = [
            'file'   => rel($appRoot, $file),
            'reason' => 'Never loaded — no discovery pattern matches this path.',
            'fix'    => explainMiss($modulesPath, $file),
        ];
    }
}

// Partition rather than skip. An ignored finding is still shown — it just does
// not gate the exit code. Hiding it outright would reproduce the exact failure
// this tool exists to expose.
foreach ($problems as $i => $p) {
    if ($pattern = matchIgnore($p['file'], $ignorePatterns)) {
        $p['pattern'] = $pattern;
        $ignored[] = $p;
        unset($problems[$i]);
    }
}
$problems = array_values($problems);

if (!$quiet) {
    echo PHP_EOL . 'upMVC doctor' . PHP_EOL;
    echo str_repeat('-', 60) . PHP_EOL;
    foreach ($modulePaths as $p) {
        echo '  scanned  ' . rel($appRoot, str_replace('\\', '/', $p)) . PHP_EOL;
    }
    echo '  loaded   ' . $seenOk . ' module(s)' . PHP_EOL;
    if ($ignored !== []) {
        echo '  ignored  ' . count($ignored) . ' finding(s) via .doctorignore' . PHP_EOL;
    }
    echo PHP_EOL;
}

if ($problems === []) {
    if (!$quiet) {
        echo '  No problems found.' . PHP_EOL . PHP_EOL;
    }
} else {
    echo '  ' . count($problems) . ' problem(s) found:' . PHP_EOL . PHP_EOL;
    foreach ($problems as $p) {
        echo '  ! ' . $p['file'] . PHP_EOL;
        echo '      ' . $p['reason'] . PHP_EOL;
        echo '      fix: ' . $p['fix'] . PHP_EOL . PHP_EOL;
    }
}

if ($ignored !== [] && !$quiet) {
    echo '  Ignored (listed in .doctorignore, not counted):' . PHP_EOL . PHP_EOL;
    foreach ($ignored as $p) {
        echo '  - ' . $p['file'] . '   [' . $p['pattern'] . ']' . PHP_EOL;
        echo '      ' . $p['reason'] . PHP_EOL . PHP_EOL;
    }
    echo '  Run with --strict to treat these as problems again.' . PHP_EOL . PHP_EOL;
}

exit($problems === [] ? 0 : 1);

// ---------------------------------------------------------------------------

function printUsage(): void
{
    echo <<<TXT
    doctor.php — report modules the kernel cannot see

      php src/Tools/doctor.php                 check every registered module path
      php src/Tools/doctor.php --quiet         only print problems
      php src/Tools/doctor.php --strict        ignore .doctorignore; report everything
      php src/Tools/doctor.php --root=/path    explicit app root (auto-detected otherwise)

    Discovery matches these three shapes only:
      Modules/{Name}/Routes/Routes.php
      Modules/{Parent}/Modules/{Name}/Routes/Routes.php
      Modules/{A}/Modules/{B}/Modules/{Name}/Routes/Routes.php

    Known-and-accepted findings go in a .doctorignore at the app root: one
    path or glob per line, # for comments. Ignored findings are still printed,
    they just do not affect the exit code.

    Exit codes: 0 clean, 1 problems found, 2 bad usage.

    TXT;
}

/**
 * Read .doctorignore from the app root. One repo-relative path or glob per
 * line; blank lines and # comments skipped.
 */
function loadIgnoreList(string $appRoot): array
{
    $file = $appRoot . '/.doctorignore';
    if (!is_file($file)) {
        return [];
    }

    $out = [];
    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        $out[] = str_replace('\\', '/', $line);
    }
    return $out;
}

/** Return the pattern that matched, or null. Globs via fnmatch, case-insensitive. */
function matchIgnore(string $relPath, array $patterns): ?string
{
    foreach ($patterns as $pattern) {
        if (strcasecmp($relPath, $pattern) === 0) {
            return $pattern;
        }
        if (fnmatch($pattern, $relPath, FNM_CASEFOLD)) {
            return $pattern;
        }
    }
    return null;
}

/** The three globs InitModsImproved actually uses. */
function discoverFiles(string $modulesPath): array
{
    $out = [];
    foreach ([
        '/*/Routes/Routes.php',
        '/*/Modules/*/Routes/Routes.php',
        '/*/Modules/*/Modules/*/Routes/Routes.php',
    ] as $pattern) {
        foreach (glob($modulesPath . $pattern) ?: [] as $f) {
            $out[] = str_replace('\\', '/', $f);
        }
    }
    return $out;
}

/**
 * Every PHP file that lives in a directory called Routes/ (any case) anywhere
 * under the modules path. These are files someone clearly intended as routes.
 */
function routeishFiles(string $modulesPath): array
{
    $out = [];
    $it  = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($modulesPath, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($it as $f) {
        if (!$f->isFile() || strtolower($f->getExtension()) !== 'php') {
            continue;
        }
        if (strtolower(basename(dirname($f->getPathname()))) === 'routes') {
            $out[] = str_replace('\\', '/', $f->getPathname());
        }
    }
    sort($out);
    return $out;
}

/**
 * A file that DOES load here but only because the filesystem ignored case.
 * Windows and macOS match it; Linux will not, and the module will vanish from
 * a deployment with no error. Worth saying out loud, since the machine that
 * finds this bug is usually production.
 */
function casingWarning(string $file): ?string
{
    $dirName  = onDiskName(dirname($file));
    $fileName = onDiskName($file);

    if ($dirName !== 'Routes' || $fileName !== 'Routes.php') {
        return "Loads here only because this filesystem ignores case: found '$dirName/$fileName', "
            . 'discovery expects Routes/Routes.php. On Linux this module is skipped silently.';
    }
    return null;
}

/** True on-disk name, read back from the parent listing rather than the path string. */
function onDiskName(string $path): string
{
    $name   = basename($path);
    $parent = dirname($path);
    foreach (scandir($parent) ?: [] as $entry) {
        if (strcasecmp($entry, $name) === 0) {
            return $entry;
        }
    }
    return $name;
}

/** Say precisely which rule this file breaks. */
function explainMiss(string $modulesPath, string $file): string
{
    $dir = dirname($file);

    if (strcasecmp(basename($file), 'Routes.php') !== 0) {
        return 'Rename to Routes.php — the glob matches that exact filename, nothing else.';
    }

    $actual = onDiskName($dir);
    if ($actual !== 'Routes') {
        return "Rename the folder '$actual' to 'Routes' — capital R. Lowercase silently fails on Linux.";
    }

    $relative = trim(str_replace($modulesPath, '', dirname($dir)), '/');
    $depth    = substr_count($relative, '/');
    if ($depth > 0 && !str_contains('/' . $relative . '/', '/Modules/')) {
        return "Nested modules need a literal 'Modules' segment: Modules/{Parent}/Modules/{Name}/Routes/Routes.php";
    }

    return 'Path is deeper than the three supported shapes — see --help.';
}

/** Mirrors InitModsImproved::createModuleData(). */
function classNameFor(string $modulesPath, string $file): string
{
    $relative = trim(str_replace($modulesPath, '', dirname(dirname($file))), '/');
    $parts    = array_filter(explode('/', $relative), 'strlen');

    $ns = ['App', 'Modules'];
    foreach ($parts as $part) {
        $ns[] = strcasecmp($part, 'Modules') === 0 ? 'Modules' : ucfirst($part);
    }
    $ns[] = 'Routes';
    $ns[] = 'Routes';

    return implode('\\', $ns);
}

function rel(string $appRoot, string $path): string
{
    $appRoot = rtrim(str_replace('\\', '/', $appRoot), '/');
    $path    = str_replace('\\', '/', $path);
    return str_starts_with($path, $appRoot . '/') ? substr($path, strlen($appRoot) + 1) : $path;
}

function resolveAppRoot(array $args): string
{
    foreach ($args as $arg) {
        if (str_starts_with($arg, '--root=')) {
            $root = rtrim(str_replace('\\', '/', substr($arg, 7)), '/');
            if (!is_file($root . '/vendor/autoload.php')) {
                echo "[ERROR] No vendor/autoload.php under --root=$root" . PHP_EOL;
                exit(2);
            }
            return $root;
        }
    }

    $cwd = str_replace('\\', '/', getcwd() ?: '');
    if ($cwd !== '' && is_file($cwd . '/vendor/autoload.php') && is_dir($cwd . '/src/Etc')) {
        return rtrim($cwd, '/');
    }

    // Walk up, skipping candidates inside a vendor/ directory: a path-repository
    // install copies the package's own vendor/ along with it.
    $dir = str_replace('\\', '/', __DIR__);
    for ($i = 0; $i < 10; $i++) {
        if (!str_contains($dir . '/', '/vendor/') && is_file($dir . '/vendor/autoload.php')) {
            return $dir;
        }
        $parent = dirname($dir);
        if ($parent === $dir) {
            break;
        }
        $dir = $parent;
    }

    echo '[ERROR] Could not locate the application root. Pass --root=/path/to/app' . PHP_EOL;
    exit(2);
}
