<?php

namespace Tests\Unit\Tools;

use PHPUnit\Framework\TestCase;

class UpmvcNextCliTest extends TestCase
{
    private string $root;
    private string $script;
    private string $generatedDir;
    /** @var list<string> */
    private array $filesToCleanup = [];

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 3);
        $this->script = $this->root . '/src/Tools/upmvc-next.php';
        $this->generatedDir = $this->root . '/docs/agent/generated';
    }

    protected function tearDown(): void
    {
        foreach ($this->filesToCleanup as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        $this->filesToCleanup = [];
    }

    public function test_cli_script_exists(): void
    {
        $this->assertFileExists($this->script);
    }

    public function test_stdout_mode_exits_zero_and_contains_goal(): void
    {
        $result = $this->runCli([
            '--goal',
            'Add a contact form module',
            '--stdout',
        ]);

        $this->assertSame(0, $result['exit'], $result['output']);
        $this->assertStringContainsString('you are already inside the house', $result['output']);
        $this->assertStringContainsString('Add a contact form module', $result['output']);
        $this->assertStringContainsString('Workflow: `create_module`', $result['output']);
    }

    public function test_config_audit_goal_maps_to_config_audit_workflow(): void
    {
        $result = $this->runCli([
            '--goal',
            'Audit my .env config wiring',
            '--stdout',
        ]);

        $this->assertSame(0, $result['exit'], $result['output']);
        $this->assertStringContainsString('Workflow: `config_audit`', $result['output']);
    }

    public function test_default_mode_writes_generated_files(): void
    {
        $goal = 'Test agent pack output ' . uniqid('', true);

        $result = $this->runCli(['--goal', $goal]);

        $this->assertSame(0, $result['exit'], $result['output']);
        $this->assertFileExists($this->generatedDir . '/last-prompt.md');
        $this->assertFileExists($this->generatedDir . '/last-session.json');

        $session = json_decode(
            (string) file_get_contents($this->generatedDir . '/last-session.json'),
            true
        );

        $this->assertIsArray($session);
        $this->assertSame($goal, $session['goal'] ?? null);
        $this->assertArrayHasKey('scan', $session);
        $this->assertGreaterThan(0, $session['scan']['module_count'] ?? 0);

        $prompt = (string) file_get_contents($this->generatedDir . '/last-prompt.md');
        $this->assertStringContainsString($goal, $prompt);
        $this->assertStringContainsString('upmvc-knowledge.json', $prompt);

        $this->filesToCleanup[] = $this->generatedDir . '/last-prompt.md';
        $this->filesToCleanup[] = $this->generatedDir . '/last-session.json';
    }

    public function test_runs_when_saas_pack_json_missing(): void
    {
        $saasFile = $this->root . '/docs/agent/upmvc-saas-pack.json';
        $backup = $saasFile . '.bak-test';

        if (!is_file($saasFile)) {
            $this->markTestSkipped('upmvc-saas-pack.json not present in this checkout');
        }

        rename($saasFile, $backup);

        try {
            $result = $this->runCli(['--goal', 'Smoke test without SaaS pack file', '--stdout']);
            $this->assertSame(0, $result['exit'], $result['output']);
            $this->assertStringContainsString('Smoke test without SaaS pack file', $result['output']);
        } finally {
            if (is_file($backup) && !is_file($saasFile)) {
                rename($backup, $saasFile);
            }
        }
    }

    public function test_scaffold_flag_includes_scaffolds_in_output(): void
    {
        $result = $this->runCli([
            '--goal',
            'Create a Blog CRUD module',
            '--scaffold',
            '--stdout',
        ]);

        $this->assertSame(0, $result['exit'], $result['output']);
        $this->assertStringContainsString('upmvc-scaffolds.json', $result['output']);
        $this->assertStringContainsString('Module scaffolds', $result['output']);
    }

    public function test_no_scaffold_flag_omits_scaffolds_by_default(): void
    {
        $result = $this->runCli([
            '--goal',
            'Audit my .env config wiring',
            '--no-scaffold',
            '--stdout',
        ]);

        $this->assertSame(0, $result['exit'], $result['output']);
        $this->assertStringNotContainsString('upmvc-scaffolds.json', $result['output']);
    }

    /** @param list<string> $args */
    private function runCli(array $args): array
    {
        $cmd = escapeshellarg(PHP_BINARY)
            . ' '
            . escapeshellarg($this->script);

        foreach ($args as $arg) {
            $cmd .= ' ' . escapeshellarg($arg);
        }

        $output = [];
        $exitCode = 0;
        exec($cmd . ' 2>&1', $output, $exitCode);

        return [
            'output' => implode("\n", $output),
            'exit' => $exitCode,
        ];
    }
}
