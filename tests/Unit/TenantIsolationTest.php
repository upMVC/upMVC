<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Tenant Isolation Guard — boilerplate for upMVC SaaS projects.
 *
 * Scans every Model.php under src/Modules/ and fails the build if a
 * SELECT / UPDATE / DELETE touches a known tenant-owned table without
 * a tenant_id guard in the SQL string.
 *
 * HOW TO USE THIS IN YOUR PROJECT
 * --------------------------------
 * 1. Add your tenant-owned table names to TENANT_TABLES below.
 * 2. Add intentionally cross-tenant models (e.g. a public marketplace)
 *    to CROSS_TENANT_MODELS.
 * 3. For dynamic SQL builders where the full WHERE clause is assembled
 *    at runtime, annotate the prepare() call:
 *        $this->conn->prepare( // @tenant-safe WHERE … tenant_id = :tid
 *
 * @package upMVC\Tests\Unit
 */
class TenantIsolationTest extends TestCase
{
    /**
     * Tables that must always be filtered by tenant_id.
     * Extend this list for every tenant-owned table in your schema.
     */
    private const TENANT_TABLES = [
        // Add your tables here, e.g.:
        // 'cars', 'reservations', 'clients', 'invoices', 'users',
    ];

    /**
     * Module directories that are intentionally cross-tenant.
     * Queries in these files are excluded from the check.
     */
    private const CROSS_TENANT_MODELS = [
        // 'Marketplace',
    ];

    public function testNoUnguardedTenantQueries(): void
    {
        if (empty(self::TENANT_TABLES)) {
            $this->markTestSkipped(
                'TenantIsolationTest: no TENANT_TABLES defined. ' .
                'Add your tenant-owned table names to start enforcing isolation.'
            );
        }

        $modelsDir  = __DIR__ . '/../../src/Modules';
        $violations = [];

        foreach ($this->findModelFiles($modelsDir) as $file) {
            // Skip cross-tenant models
            foreach (self::CROSS_TENANT_MODELS as $exempt) {
                if (str_contains(str_replace('\\', '/', $file), '/' . $exempt . '/')) {
                    continue 2;
                }
            }

            $source = (string) file_get_contents($file);

            preg_match_all('/->prepare\(\s*["\']([^"\']+)["\']/', $source, $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                $sql  = $match[1];
                $verb = strtoupper(strtok(ltrim($sql), " \t\n") ?: '');

                if (!in_array($verb, ['SELECT', 'UPDATE', 'DELETE'], true)) {
                    continue;
                }

                // Check for @tenant-safe developer annotation
                $pos     = (int) strpos($source, $match[0]);
                $lineCtx = substr($source, max(0, $pos - 10), strlen($match[0]) + 100);
                if (str_contains($lineCtx, '@tenant-safe')) {
                    continue;
                }

                foreach (self::TENANT_TABLES as $table) {
                    if (!preg_match('/\b' . preg_quote($table, '/') . '\b/i', $sql)) {
                        continue;
                    }

                    if (stripos($sql, 'tenant_id') !== false || stripos($sql, ':tid') !== false) {
                        continue;
                    }

                    $relative    = str_replace(realpath(__DIR__ . '/../../') . DIRECTORY_SEPARATOR, '', $file);
                    $violations[] = sprintf(
                        "%s\n    Table '%s' without tenant_id:\n    SQL: %s",
                        $relative, $table,
                        substr(preg_replace('/\s+/', ' ', $sql) ?: '', 0, 120)
                    );
                }
            }
        }

        if (!empty($violations)) {
            $this->fail(
                "Tenant isolation violations — add :tenant_id to the query or use \$this->tq():\n\n" .
                implode("\n\n", $violations)
            );
        }

        $this->assertTrue(true); // all clear
    }

    private function findModelFiles(string $dir): array
    {
        if (!is_dir($dir)) {
            return [];
        }
        $files = [];
        $it    = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
        foreach ($it as $file) {
            /** @var \SplFileInfo $file */
            if ($file->isFile() && $file->getFilename() === 'Model.php') {
                $files[] = $file->getPathname();
            }
        }
        return $files;
    }
}
