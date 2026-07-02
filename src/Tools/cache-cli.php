<?php
/**
 * cache-cli.php - upMVC Cache Maintenance Utility
 *
 * Usage (from project root):
 *   php src/Tools/cache-cli.php list
 *   php src/Tools/cache-cli.php stats
 *   php src/Tools/cache-cli.php clear:modules
 *   php src/Tools/cache-cli.php clear:admin
 *   php src/Tools/cache-cli.php clear:all
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Etc\InitModsImproved;
use App\Etc\Cache\CacheManager;
use App\Modules\Admin\Routes\Routes as AdminRoutes;

function color($text, $code) {
    return (DIRECTORY_SEPARATOR === '\\') ? $text : "\033[{$code}m{$text}\033[0m";
}

function out($text) { echo $text . PHP_EOL; }

function help() {
    out('upMVC Cache CLI Commands:');
    out('  list           Show available commands');
    out('  stats          Show cache-related statistics');
    out('  clear:modules  Clear module discovery caches');
    out('  clear:admin    Clear admin dynamic route cache');
    out('  clear:all      Flush all cache stores + module + admin caches');
    out('  help           Show this help');
}

$cmd = $argv[1] ?? 'help';

function ensureStorageDirs(): void {
    $paths = [
        __DIR__ . '/../Etc/storage/cache',
    ];
    foreach ($paths as $p) {
        if (!is_dir($p)) {
            @mkdir($p, 0755, true);
        }
    }
}

ensureStorageDirs();

$adminCacheFile = __DIR__ . '/../Etc/storage/cache/admin_routes.php';

switch ($cmd) {
    case 'list':
    case 'help':
        help();
        exit(0);

    case 'stats':
        $init = new InitModsImproved();
        $stats = $init->getStats();
        $adminExists = file_exists($adminCacheFile);
        $adminSize = $adminExists ? filesize($adminCacheFile) : 0;
        $adminAge = $adminExists ? (time() - filemtime($adminCacheFile)) : 0;
        out(color('Module Discovery Cache:', '36'));
        foreach ($stats as $k => $v) {
            out("  - {$k}: {$v}");
        }
        out(color('Admin Route Cache:', '33'));
        out('  - exists: ' . ($adminExists ? 'yes' : 'no'));
        out('  - size(bytes): ' . $adminSize);
        out('  - age(seconds): ' . $adminAge);
        exit(0);

    case 'clear:modules':
        try {
            $init = new InitModsImproved();
            $init->clearCache();
            out(color('Module discovery cache cleared.', '32'));
            exit(0);
        } catch (Exception $e) {
            out(color('Failed to clear module cache: ' . $e->getMessage(), '31'));
            exit(1);
        }

    case 'clear:admin':
        try {
            if (class_exists(AdminRoutes::class)) {
                AdminRoutes::clearCache();
                out(color('Admin dynamic route cache cleared.', '32'));
                exit(0);
            }
            out(color('AdminRoutes class not found; nothing to clear.', '33'));
            exit(0);
        } catch (Exception $e) {
            out(color('Failed to clear admin cache: ' . $e->getMessage(), '31'));
            exit(1);
        }

    case 'clear:all':
        $errors = 0;
        try {
            $init = new InitModsImproved();
            $init->clearCache();
            out(color('[OK] Module discovery cache', '32'));
        } catch (Exception $e) {
            out(color('[FAIL] Module discovery: ' . $e->getMessage(), '31'));
            $errors++;
        }
        try {
            if (class_exists(AdminRoutes::class)) {
                AdminRoutes::clearCache();
                out(color('[OK] Admin route cache', '32'));
            } else {
                out(color('[SKIP] Admin route cache (class missing)', '33'));
            }
        } catch (Exception $e) {
            out(color('[FAIL] Admin route cache: ' . $e->getMessage(), '31'));
            $errors++;
        }
        try {
            CacheManager::clearAll();
            out(color('[OK] Cache stores flushed', '32'));
        } catch (Exception $e) {
            out(color('[FAIL] Cache stores: ' . $e->getMessage(), '31'));
            $errors++;
        }
        exit($errors === 0 ? 0 : 1);

    default:
        out(color('Unknown command: ' . $cmd, '31'));
        help();
        exit(2);
}
