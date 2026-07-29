#!/usr/bin/env php
<?php

/**
 * migrate.php — upMVC migration runner
 *
 * Usage:
 *   php src/Tools/migrate.php                # run all pending migrations
 *   php src/Tools/migrate.php --status       # list applied / pending / missing
 *   php src/Tools/migrate.php --dump         # (re)generate database/schema.sql
 *   php src/Tools/migrate.php --baseline     # record migrations as applied WITHOUT running them
 *   php src/Tools/migrate.php --fresh        # drop tracking table and re-run all (dev only)
 *   php src/Tools/migrate.php --root=/path   # explicit app root (auto-detected otherwise)
 *
 * --baseline is for databases built by importing database/schema.sql rather
 * than by running migrations. Their tracking table is empty, so a later run
 * would replay every migration from the beginning. Baselining marks the
 * current set as applied so only genuinely new migrations run afterwards.
 *
 * Migrations are collected from every path registered on App\Etc\Application,
 * in run order: kernel → packages → app. Packages register their own path from
 * a service provider, e.g. the SaaS pack:
 *
 *     $app->addMigrationPath($packRoot . '/database/migrations');
 *
 * Reads DB credentials from src/Etc/.env — the same file the app uses.
 * Never run --fresh against production.
 *
 * @package upMVC
 * @license MIT
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("migrate.php is CLI only\n");
}

$args = array_slice($argv, 1);

// Reject anything unrecognised. Falling through to a real migration run
// because of a typo — or a --help nobody implemented — is how you end up
// migrating the wrong database.
$known = ['--status', '--dump', '--baseline', '--fresh'];
foreach ($args as $arg) {
    if (in_array($arg, $known, true) || str_starts_with($arg, '--root=')) {
        continue;
    }
    if ($arg === '--help' || $arg === '-h') {
        printUsage();
        exit(0);
    }
    echo "[ERROR] Unknown option: $arg" . PHP_EOL . PHP_EOL;
    printUsage();
    exit(1);
}

$fresh    = in_array('--fresh', $args, true);
$status   = in_array('--status', $args, true);
$dump     = in_array('--dump', $args, true);
$baseline = in_array('--baseline', $args, true);

$appRoot = resolveAppRoot($args);

if (!defined('UPMVC_APP_ROOT')) {
    define('UPMVC_APP_ROOT', $appRoot);
}

require_once $appRoot . '/vendor/autoload.php';

use App\Etc\Application;
use App\Etc\Config\ConfigManager;
use App\Etc\Config\Environment;

ConfigManager::load();

// register() is what lets packages contribute migration paths; boot() needs a
// Router and is deliberately not called here.
$app = Application::getInstance();
$app->registerProviders();

$paths = $app->getMigrationPathsForRun();
$pdo   = connectDb();

ensureMigrationsTable($pdo, $fresh);

$migrations = collectMigrations($paths);

if ($status) {
    printStatus($pdo, $migrations);
    exit(0);
}

if ($dump) {
    dumpSchema($pdo, $appRoot);
    exit(0);
}

if ($baseline) {
    baselineMigrations($pdo, $migrations);
    exit(0);
}

runPending($pdo, $migrations);
dumpSchema($pdo, $appRoot);
exit(0);

// ---------------------------------------------------------------------------

function printUsage(): void
{
    echo <<<TXT
    migrate.php — upMVC migration runner

      php src/Tools/migrate.php                run all pending migrations
      php src/Tools/migrate.php --status       list applied / pending / missing
      php src/Tools/migrate.php --dump         regenerate database/schema.sql
      php src/Tools/migrate.php --baseline     record migrations as applied without running them
      php src/Tools/migrate.php --fresh        drop tracking table and re-run all (dev only)
      php src/Tools/migrate.php --root=/path   explicit app root (auto-detected otherwise)

    Database credentials come from src/Etc/.env — check DB_NAME before running.

    TXT;
}

function resolveAppRoot(array $args): string
{
    foreach ($args as $arg) {
        if (str_starts_with($arg, '--root=')) {
            $root = rtrim(str_replace('\\', '/', substr($arg, 7)), '/');
            if (!is_file($root . '/vendor/autoload.php')) {
                fail("No vendor/autoload.php under --root=$root");
            }
            return $root;
        }
    }

    // Normal invocation is `php vendor/bitshost/upmvc/src/Tools/migrate.php` from
    // the app root, so trust the working directory when it looks like an app.
    $cwd = str_replace('\\', '/', getcwd() ?: '');
    if ($cwd !== '' && is_file($cwd . '/vendor/autoload.php') && is_dir($cwd . '/src/Etc')) {
        return rtrim($cwd, '/');
    }

    // Otherwise walk up from this file until a Composer autoloader appears.
    // Candidates inside a vendor/ directory are skipped: a path-repository
    // install copies the package's own vendor/ along with it, so
    // vendor/bitshost/upmvc/vendor/autoload.php exists and would otherwise be
    // mistaken for the application root.
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

    fail('Could not locate the application root (no vendor/autoload.php found). Pass --root=/path/to/app');
}

function connectDb(): PDO
{
    $host    = Environment::get('DB_HOST', '127.0.0.1');
    $name    = Environment::get('DB_NAME', '');
    $user    = Environment::get('DB_USER', '');
    $pass    = Environment::get('DB_PASS', '');
    $port    = Environment::get('DB_PORT', '3306');
    $charset = Environment::get('DB_CHARSET', 'utf8mb4');

    if ($name === '') {
        fail('DB_NAME is not set in src/Etc/.env');
    }

    try {
        $pdo = new PDO(
            "mysql:host=$host;port=$port;dbname=$name;charset=$charset",
            (string) $user,
            (string) $pass,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    } catch (PDOException $e) {
        fail('DB connection failed: ' . $e->getMessage());
    }

    out("Connected to '$name' on $host:$port");
    return $pdo;
}

function ensureMigrationsTable(PDO $pdo, bool $fresh): void
{
    if ($fresh) {
        $pdo->exec('DROP TABLE IF EXISTS migrations');
        out('[--fresh] Dropped migrations table — everything will re-run.');
    }

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS migrations (
            id         INT AUTO_INCREMENT PRIMARY KEY,
            filename   VARCHAR(255) NOT NULL UNIQUE,
            applied_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
}

/**
 * Collect migrations from every registered path, preserving path order and
 * sorting by filename within each path.
 *
 * Tracking is by bare filename so the table stays readable, which means a
 * filename may only appear once across all paths. A collision would silently
 * skip a migration, so it is a hard error instead.
 *
 * @param array<int, string> $paths
 * @return array<int, array{name: string, file: string}>
 */
function collectMigrations(array $paths): array
{
    if ($paths === []) {
        out('No migration paths registered.');
        return [];
    }

    $collected = [];
    $seen      = [];

    foreach ($paths as $path) {
        $files = glob($path . '/*.sql') ?: [];
        sort($files, SORT_STRING);

        foreach ($files as $file) {
            $name = basename($file);

            if (isset($seen[$name])) {
                fail(
                    "Duplicate migration filename '$name'\n"
                    . "          also at: {$seen[$name]}\n"
                    . "          and:     $file\n"
                    . '        Migration filenames must be unique across kernel, packages and app.'
                );
            }

            $seen[$name]  = $file;
            $collected[]  = ['name' => $name, 'file' => $file];
        }
    }

    return $collected;
}

/** @return array<int, string> */
function appliedMigrations(PDO $pdo): array
{
    return $pdo->query('SELECT filename FROM migrations ORDER BY filename')
               ->fetchAll(PDO::FETCH_COLUMN);
}

/** @param array<int, array{name: string, file: string}> $migrations */
function runPending(PDO $pdo, array $migrations): void
{
    if ($migrations === []) {
        out('No migration files found.');
        return;
    }

    $applied = appliedMigrations($pdo);
    $ran     = 0;

    foreach ($migrations as $migration) {
        ['name' => $name, 'file' => $file] = $migration;

        if (in_array($name, $applied, true)) {
            out("  SKIP   $name");
            continue;
        }

        $sql = file_get_contents($file);
        if ($sql === false || trim($sql) === '') {
            out("  WARN   $name is empty — skipped");
            continue;
        }

        out("  RUN    $name");

        try {
            $pdo->exec($sql);
            $pdo->prepare('INSERT INTO migrations (filename) VALUES (:f)')
                ->execute([':f' => $name]);
            out("  OK     $name");
            $ran++;
        } catch (PDOException $e) {
            fail("Migration '$name' failed: " . $e->getMessage());
        }
    }

    out($ran === 0 ? 'Nothing to migrate — database is up to date.' : "Done. $ran migration(s) applied.");
}

/**
 * Record migrations as applied without executing them.
 *
 * For databases created by importing database/schema.sql: the structure is
 * already correct but the tracking table is empty, so a normal run would
 * replay history. This marks the current set as applied so only genuinely new
 * migrations run later.
 *
 * @param array<int, array{name: string, file: string}> $migrations
 */
function baselineMigrations(PDO $pdo, array $migrations): void
{
    if ($migrations === []) {
        out('No migration files found — nothing to baseline.');
        return;
    }

    // Guard: baselining an empty database would mark the schema as created
    // when it never was, leaving a database that can never be built.
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    $tables = array_filter($tables, static fn($t): bool => $t !== 'migrations');

    if ($tables === []) {
        fail(
            "Refusing to baseline an empty database.\n"
            . "        --baseline records migrations as applied WITHOUT running them.\n"
            . '        Run migrate normally, or import database/schema.sql first.'
        );
    }

    $applied = appliedMigrations($pdo);
    $marked  = 0;

    foreach ($migrations as $migration) {
        if (in_array($migration['name'], $applied, true)) {
            out("  SKIP   {$migration['name']} (already recorded)");
            continue;
        }

        $pdo->prepare('INSERT INTO migrations (filename) VALUES (:f)')
            ->execute([':f' => $migration['name']]);

        out("  MARK   {$migration['name']}");
        $marked++;
    }

    out($marked === 0
        ? 'Nothing to baseline — every migration was already recorded.'
        : "Done. $marked migration(s) recorded as applied. Nothing was executed.");
}

/** @param array<int, array{name: string, file: string}> $migrations */
function printStatus(PDO $pdo, array $migrations): void
{
    $applied = appliedMigrations($pdo);
    $known   = [];

    out(str_pad('STATUS', 11) . 'MIGRATION');
    out(str_repeat('-', 60));

    foreach ($migrations as $migration) {
        $known[] = $migration['name'];
        $label   = in_array($migration['name'], $applied, true) ? '[applied]' : '[pending]';
        out(str_pad($label, 11) . $migration['name']);
    }

    foreach ($applied as $name) {
        if (!in_array($name, $known, true)) {
            out(str_pad('[missing]', 11) . $name . '  ← applied, but no file found');
        }
    }
}

/**
 * Write database/schema.sql from the live database.
 *
 * Generated, never hand-edited — a hand-maintained snapshot drifts from the
 * migrations that produced it.
 */
function dumpSchema(PDO $pdo, string $appRoot): void
{
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    $tables = array_values(array_filter($tables, static fn($t): bool => $t !== 'migrations'));
    sort($tables, SORT_STRING);

    if ($tables === []) {
        out('Nothing to dump — no tables.');
        return;
    }

    $out = "-- ============================================================\n"
         . "-- schema.sql — GENERATED FILE, DO NOT EDIT BY HAND\n"
         . "--\n"
         . "-- Produced by: php src/Tools/migrate.php --dump\n"
         . "-- Generated:   " . date('Y-m-d H:i:s') . "\n"
         . "--\n"
         . "-- Full current structure, for fresh installs and for reading the\n"
         . "-- data model without running anything. The migrations remain the\n"
         . "-- source of truth; this file is derived from them.\n"
         . "-- ============================================================\n\n"
         . "SET FOREIGN_KEY_CHECKS = 0;\n\n";

    foreach ($tables as $table) {
        $row = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM);
        if (!$row || !isset($row[1])) {
            continue;
        }
        $create = preg_replace('/AUTO_INCREMENT=\d+\s*/', '', (string) $row[1]);
        $out   .= "-- ---------- $table ----------\n"
                . str_replace('CREATE TABLE', 'CREATE TABLE IF NOT EXISTS', (string) $create)
                . ";\n\n";
    }

    $out .= "SET FOREIGN_KEY_CHECKS = 1;\n";

    $dir = $appRoot . '/database';
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        fail("Could not create $dir");
    }

    file_put_contents($dir . '/schema.sql', $out);
    out('Wrote ' . count($tables) . " table(s) to database/schema.sql");
}

function out(string $msg): void
{
    echo $msg . PHP_EOL;
}

function fail(string $msg): never
{
    echo '[ERROR] ' . $msg . PHP_EOL;
    exit(1);
}
