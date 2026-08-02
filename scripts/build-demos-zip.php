<?php
/**
 * Build the optional demo-modules zip for GitHub Releases.
 *
 * Usage: php scripts/build-demos-zip.php
 * Output: dist/upmvc-demos.zip (gitignored — attach to a Release)
 *
 * Does NOT include Welcome (that stays in the thin create-project).
 */

$root = dirname(__DIR__);
$modulesDir = $root . '/src/Modules';
$outDir = $root . '/dist';
$zipPath = $outDir . '/upmvc-demos.zip';

if (!is_dir($modulesDir)) {
    fwrite(STDERR, "Modules directory missing: {$modulesDir}\n");
    exit(1);
}

if (!is_dir($outDir) && !mkdir($outDir, 0775, true) && !is_dir($outDir)) {
    fwrite(STDERR, "Cannot create dist/\n");
    exit(1);
}

if (file_exists($zipPath)) {
    unlink($zipPath);
}

$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
    fwrite(STDERR, "Cannot create zip: {$zipPath}\n");
    exit(1);
}

$readme = <<<'MD'
# upMVC demo modules

Optional show-off modules for upMVC. Not required for the kernel or for
building your own app.

## Install

1. Unzip so each folder lands under your project's `src/Modules/`
   (Auth, Test, User, … next to Welcome).
2. If a demo needs tables: `mysql … < database/demo-modules.sql`
   (file is included in this archive as `demo-modules.sql`).
3. `composer dump-autoload` if Composer does not pick up new classes
   (usually unnecessary — `App\` → `src/` already covers them).
4. Hit the routes (`/test`, `/auth`, `/admin`, …).

## Uninstall

Delete the module folders you do not want. No Composer package lifecycle —
demos are take / try / drop.

## Note

`Welcome` is **not** in this zip — it ships with the core repo and owns `/`
via `src/Etc/custom-routes.php`. Demo modules must not re-register `/`.
MD;

$zip->addFromString('README.md', $readme);

$demoSql = $root . '/database/demo-modules.sql';
if (is_file($demoSql)) {
    $zip->addFile($demoSql, 'demo-modules.sql');
}

$skip = ['Welcome', '.', '..'];
$dirs = scandir($modulesDir) ?: [];
$count = 0;

foreach ($dirs as $name) {
    if (in_array($name, $skip, true)) {
        continue;
    }
    $path = $modulesDir . '/' . $name;
    if (!is_dir($path)) {
        continue;
    }
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($iterator as $file) {
        /** @var SplFileInfo $file */
        if (!$file->isFile()) {
            continue;
        }
        $full = $file->getPathname();
        $relative = 'Modules/' . $name . '/' . str_replace('\\', '/', substr($full, strlen($path) + 1));
        $zip->addFile($full, $relative);
    }
    $count++;
    echo "  + {$name}\n";
}

$zip->close();

$size = filesize($zipPath);
echo "Wrote {$zipPath} ({$count} modules, " . round($size / 1024 / 1024, 2) . " MB)\n";
