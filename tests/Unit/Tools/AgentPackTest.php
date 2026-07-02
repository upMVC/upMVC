<?php

namespace Tests\Unit\Tools;

use PHPUnit\Framework\TestCase;

class AgentPackTest extends TestCase
{
    private string $root;
    private string $agentDir;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 3);
        $this->agentDir = $this->root . '/docs/agent';
    }

    public function test_agent_pack_json_files_exist_and_parse(): void
    {
        $files = [
            'upmvc-knowledge.json',
            'upmvc-rules.json',
            'upmvc-workflows.json',
            'upmvc-saas-pack.json',
        ];

        foreach ($files as $file) {
            $path = $this->agentDir . '/' . $file;
            $this->assertFileExists($path, $file . ' should exist');

            $data = json_decode((string) file_get_contents($path), true);
            $this->assertIsArray($data, $file . ' should decode to array');
            $this->assertArrayHasKey('meta', $data, $file . ' should have meta key');
        }
    }

    public function test_rules_pack_has_must_and_never_arrays(): void
    {
        $data = json_decode(
            (string) file_get_contents($this->agentDir . '/upmvc-rules.json'),
            true
        );

        $this->assertIsArray($data);
        $this->assertNotEmpty($data['must'] ?? []);
        $this->assertNotEmpty($data['never'] ?? []);
    }

    public function test_workflows_pack_has_create_module_recipe(): void
    {
        $data = json_decode(
            (string) file_get_contents($this->agentDir . '/upmvc-workflows.json'),
            true
        );

        $this->assertIsArray($data);
        $this->assertArrayHasKey('create_module', $data['workflows'] ?? []);
    }

    public function test_agents_md_points_to_agent_pack(): void
    {
        $path = $this->root . '/AGENTS.md';
        $this->assertFileExists($path);

        $content = (string) file_get_contents($path);
        $this->assertStringContainsString('docs/agent/upmvc-knowledge.json', $content);
        $this->assertStringContainsString('upmvc-next.php', $content);
        $this->assertStringContainsString('docs/AGENT_PACK.md', $content);
    }

    public function test_agent_pack_guide_exists(): void
    {
        $path = $this->root . '/docs/AGENT_PACK.md';
        $this->assertFileExists($path);

        $content = (string) file_get_contents($path);
        $this->assertStringContainsString('upmvc-next.php', $content);
        $this->assertStringContainsString('upmvc-rules.json', $content);
    }

    public function test_agent_pack_json_not_stale(): void
    {
        $knowledge = json_decode(
            (string) file_get_contents($this->agentDir . '/upmvc-knowledge.json'),
            true
        );
        $rules = json_decode(
            (string) file_get_contents($this->agentDir . '/upmvc-rules.json'),
            true
        );

        $this->assertIsString($knowledge['meta']['version'] ?? null);
        $this->assertNotEmpty($knowledge['meta']['version'] ?? null);
        $this->assertStringContainsString('v2.3', (string) ($knowledge['meta']['verified_against'] ?? ''));

        $rulesBlob = json_encode($rules);
        $this->assertIsString($rulesBlob);
        $this->assertStringContainsString('InitModsImproved', $rulesBlob);
        $this->assertStringNotContainsString('InitMods when', $rulesBlob);

        $saasPath = $this->agentDir . '/upmvc-saas-pack.json';
        if (!is_file($saasPath)) {
            return;
        }

        $saas = json_decode((string) file_get_contents($saasPath), true);
        $saasBlob = json_encode($saas);
        $this->assertIsString($saasBlob);
        $this->assertStringContainsString('addRoute', $saasBlob);
        $this->assertStringNotContainsString('->middleware(', $saasBlob);
        $this->assertStringNotContainsString('Billing', $saasBlob);
    }

    public function test_scaffolds_pack_is_optional_extension(): void
    {
        $path = $this->agentDir . '/upmvc-scaffolds.json';
        $this->assertFileExists($path);

        $data = json_decode((string) file_get_contents($path), true);
        $this->assertIsArray($data);
        $this->assertTrue($data['meta']['optional'] ?? false);
        $this->assertArrayHasKey('basic', $data['module_types'] ?? []);
        $this->assertArrayHasKey('crud', $data['module_types'] ?? []);

        $knowledge = json_decode(
            (string) file_get_contents($this->agentDir . '/upmvc-knowledge.json'),
            true
        );
        $this->assertStringContainsString('upmvc-scaffolds.json', (string) ($knowledge['modules']['generator'] ?? ''));
    }

    public function test_legacy_generators_removed(): void
    {
        $removed = [
            'src/Tools/createmodule',
            'src/Tools/modulegenerator',
            'src/Tools/crudgenerator',
            'src/Tools/modulegenerator-enhanced',
            'src/Tools/ModuleGeneratorEnhanced',
        ];

        foreach ($removed as $dir) {
            $this->assertDirectoryDoesNotExist($this->root . '/' . $dir);
        }
    }
}
