#!/usr/bin/env php
<?php
/**
 * migrate.php — CLI migration runner
 *
 * Usage:
 *   php database/migrate.php            # run all pending migrations
 *   php database/migrate.php --status   # list applied / pending
 *   php database/migrate.php --fresh    # drop migrations table and re-run all (dev only)
 *
 * Reads DB credentials from src/Etc/.env  (same file the app uses).
 * Never run --fresh on production.
 */

define('MIGRATIONS_DIR', __DIR__ . '/migrations');
define('ENV_FILE',       __DIR__ . '/../src/Etc/.env');

// -----------------------------------------------------------------------
// Bootstrap
// -----------------------------------------------------------------------

$args  = array_slice($argv ?? [], 1);
$fresh = in_array('--fresh', $args, true);
$status = in_array('--status', $args, true);

$env = loadEnv(ENV_FILE);
$pdo = connectDb($env);

ensureMigrationsTable($pdo, $fresh);

// -----------------------------------------------------------------------
// Commands
// -----------------------------------------------------------------------

if ($status) {
    printStatus($pdo);
    exit(0);
}

runPending($pdo, $fresh);
exit(0);

// -----------------------------------------------------------------------
// Functions
// -----------------------------------------------------------------------

function loadEnv(string $path): array
{
    if (!file_exists($path)) {
        fail("Cannot find .env at: $path");
    }

    $env = [];
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (str_contains($line, '=')) {
            [$key, $val] = explode('=', $line, 2);
            $env[trim($key)] = trim($val);
        }
    }
    return $env;
}

function connectDb(array $env): PDO
{
    $host    = $env['DB_HOST']    ?? '127.0.0.1';
    $db      = $env['DB_NAME']    ?? '';
    $user    = $env['DB_USER']    ?? '';
    $pass    = $env['DB_PASS']    ?? '';
    $port    = $env['DB_PORT']    ?? '3306';
    $charset = $env['DB_CHARSET'] ?? 'utf8mb4';

    if ($db === '') {
        fail('DB_NAME is not set in .env');
    }

    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        out("Connected to database '$db' on $host:$port");
        return $pdo;
    } catch (PDOException $e) {
        fail('DB connection failed: ' . $e->getMessage());
    }
}

function ensureMigrationsTable(PDO $pdo, bool $fresh): void
{
    if ($fresh) {
        $pdo->exec("DROP TABLE IF EXISTS migrations");
        out("[--fresh] Dropped migrations table — all migrations will re-run.");
    }

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS migrations (
            id         INT AUTO_INCREMENT PRIMARY KEY,
            filename   VARCHAR(255) NOT NULL UNIQUE,
            applied_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
}

function appliedMigrations(PDO $pdo): array
{
    return $pdo->query("SELECT filename FROM migrations ORDER BY filename")
               ->fetchAll(PDO::FETCH_COLUMN);
}

function migrationFiles(): array
{
    $files = glob(MIGRATIONS_DIR . '/*.sql');
    if ($files === false || empty($files)) {
        return [];
    }
    sort($files);
    return $files;
}

function runPending(PDO $pdo, bool $fresh): void
{
    $applied = appliedMigrations($pdo);
    $files   = migrationFiles();

    if (empty($files)) {
        out('No migration files found in ' . MIGRATIONS_DIR);
        return;
    }

    $ran = 0;
    foreach ($files as $file) {
        $name = basename($file);

        if (in_array($name, $applied, true)) {
            out("  SKIP   $name (already applied)");
            continue;
        }

        out("  RUN    $name ...");
        $sql = file_get_contents($file);

        if ($sql === false || trim($sql) === '') {
            out("  WARN   $name is empty — skipping.");
            continue;
        }

        try {
            $pdo->exec($sql);
            $stmt = $pdo->prepare("INSERT INTO migrations (filename) VALUES (:f)");
            $stmt->execute([':f' => $name]);
            out("  OK     $name");
            $ran++;
        } catch (PDOException $e) {
            fail("Migration '$name' failed: " . $e->getMessage());
        }
    }

    if ($ran === 0) {
        out('Nothing to migrate — database is up to date.');
    } else {
        out("Done. $ran migration(s) applied.");
    }
}

function printStatus(PDO $pdo): void
{
    $applied = appliedMigrations($pdo);
    $files   = migrationFiles();

    out(str_pad('STATUS', 10) . 'MIGRATION');
    out(str_repeat('-', 50));

    foreach ($files as $file) {
        $name   = basename($file);
        $label  = in_array($name, $applied, true) ? '[applied]' : '[pending]';
        out(str_pad($label, 10) . $name);
    }

    // Show orphans (applied but file gone)
    foreach ($applied as $name) {
        if (!file_exists(MIGRATIONS_DIR . '/' . $name)) {
            out(str_pad('[missing]', 10) . $name . '  ← file deleted');
        }
    }
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
