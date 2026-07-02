<?php
/**
 * upmvc-next.php — upMVC Agent prompt builder
 *
 * Puts the user in the middle of the house: scan project, ask what they want,
 * output a prompt package for any AI agent (Cursor, Claude, API, etc.).
 *
 * Usage (from project root):
 *   php src/Tools/upmvc-next.php
 *   php src/Tools/upmvc-next.php --goal "Add a bookings API for my SaaS"
 *   php src/Tools/upmvc-next.php --scaffold
 *   php src/Tools/upmvc-next.php --no-scaffold
 *
 * Output:
 *   docs/agent/generated/last-prompt.md
 *   docs/agent/generated/last-session.json
 */

declare(strict_types=1);

final class UpmvcNext
{
    private string $appRoot;
    private string $agentDir;
    private string $generatedDir;

    /** @var array<string, mixed> */
    private array $knowledge = [];
    /** @var array<string, mixed> */
    private array $rules = [];
    /** @var array<string, mixed> */
    private array $workflows = [];
    /** @var array<string, mixed> */
    private array $saasPack = [];
    /** @var array<string, mixed> */
    private array $scaffolds = [];

    public function __construct(?string $appRoot = null)
    {
        $this->appRoot = $this->resolveAppRoot($appRoot);
        $this->agentDir = $this->appRoot . '/docs/agent';
        $this->generatedDir = $this->agentDir . '/generated';
    }

    public function run(array $argv): int
    {
        $parsed = $this->parseArgv($argv);
        $goal = $parsed['goal'];
        $stdoutOnly = $parsed['stdout'];
        $scaffoldFlag = $parsed['scaffold_flag'];

        if (!$this->loadAgentPack()) {
            return 1;
        }

        $scan = $this->scanProject();
        $this->banner($scan);

        if ($goal === null || trim($goal) === '') {
            $goal = $this->askGoal();
        }

        $workflow = $this->matchWorkflow($goal);
        $includeScaffolds = $this->resolveIncludeScaffolds($goal, $workflow, $scaffoldFlag);

        if ($includeScaffolds && !$this->loadScaffoldsPack()) {
            return 1;
        }

        $session = $this->buildSession($scan, $goal, $workflow, $includeScaffolds);
        $prompt = $this->buildPrompt($session);

        if ($stdoutOnly) {
            echo $prompt;
            return 0;
        }

        if (!$this->writeOutput($session, $prompt)) {
            return 1;
        }

        $this->printDone();
        return 0;
    }

    private function resolveAppRoot(?string $appRoot): string
    {
        if ($appRoot !== null && is_dir($appRoot)) {
            return realpath($appRoot) ?: $appRoot;
        }

        if (defined('UPMVC_APP_ROOT') && is_dir((string) UPMVC_APP_ROOT)) {
            return realpath((string) UPMVC_APP_ROOT) ?: (string) UPMVC_APP_ROOT;
        }

        $candidate = realpath(__DIR__ . '/../..');
        if ($candidate !== false && is_dir($candidate . '/src/Etc')) {
            return $candidate;
        }

        return getcwd() ?: '.';
    }

    /**
     * @return array{goal: ?string, stdout: bool, scaffold_flag: ?bool}
     */
    private function parseArgv(array $argv): array
    {
        $goal = null;
        $stdout = in_array('--stdout', $argv, true);
        $scaffoldFlag = null;

        if (in_array('--scaffold', $argv, true)) {
            $scaffoldFlag = true;
        } elseif (in_array('--no-scaffold', $argv, true)) {
            $scaffoldFlag = false;
        }

        foreach ($argv as $i => $arg) {
            if ($arg === '--goal' && isset($argv[$i + 1])) {
                $goal = $argv[$i + 1];
            }
        }

        return ['goal' => $goal, 'stdout' => $stdout, 'scaffold_flag' => $scaffoldFlag];
    }

    private function loadScaffoldsPack(): bool
    {
        $path = $this->agentDir . '/upmvc-scaffolds.json';
        if (!is_file($path)) {
            $this->err("Scaffold pack requested but missing: {$path}");
            return false;
        }

        $decoded = json_decode((string) file_get_contents($path), true);
        if (!is_array($decoded)) {
            $this->err("Invalid JSON: {$path}");
            return false;
        }

        $this->scaffolds = $decoded;
        return true;
    }

    private function hasScaffoldsFile(): bool
    {
        return is_file($this->agentDir . '/upmvc-scaffolds.json');
    }

    /**
     * @param array{id: string, label: string}|null $workflow
     */
    private function resolveIncludeScaffolds(string $goal, ?array $workflow, ?bool $scaffoldFlag): bool
    {
        if ($scaffoldFlag === true) {
            return true;
        }
        if ($scaffoldFlag === false) {
            return false;
        }

        if (!$this->looksLikeScaffoldGoal($goal, $workflow)) {
            return false;
        }

        if (PHP_SAPI !== 'cli' || !defined('STDIN')) {
            return false;
        }

        if (function_exists('stream_isatty') && !stream_isatty(STDIN)) {
            return false;
        }

        return $this->askIncludeScaffolds();
    }

    /**
     * @param array{id: string, label: string}|null $workflow
     */
    private function looksLikeScaffoldGoal(string $goal, ?array $workflow): bool
    {
        $scaffoldWorkflows = ['create_module', 'saas_domain_module', 'add_api_route'];
        if ($workflow !== null && in_array($workflow['id'], $scaffoldWorkflows, true)) {
            return true;
        }

        $lower = strtolower($goal);
        $keywords = [
            'module', 'scaffold', 'crud', 'generator', 'boilerplate', 'new page',
        ];

        foreach ($keywords as $kw) {
            if (str_contains($lower, $kw)) {
                return true;
            }
        }

        return false;
    }

    private function askIncludeScaffolds(): bool
    {
        if (!$this->hasScaffoldsFile()) {
            return false;
        }

        $this->out('This looks like module scaffolding.');
        $this->out('Load optional scaffold pack (upmvc-scaffolds.json)? [y/N]: ', false);

        $line = fgets(STDIN);
        $answer = is_string($line) ? strtolower(trim($line)) : '';

        return in_array($answer, ['y', 'yes', '1'], true);
    }

    private function loadAgentPack(): bool
    {
        $required = [
            'knowledge' => 'upmvc-knowledge.json',
            'rules' => 'upmvc-rules.json',
            'workflows' => 'upmvc-workflows.json',
        ];

        foreach ($required as $prop => $file) {
            $path = $this->agentDir . '/' . $file;
            if (!is_file($path)) {
                $this->err("Missing agent pack file: {$path}");
                $this->err('Run this from an upMVC project with docs/agent/ installed.');
                return false;
            }

            $decoded = json_decode((string) file_get_contents($path), true);
            if (!is_array($decoded)) {
                $this->err("Invalid JSON: {$path}");
                return false;
            }

            $this->{$prop} = $decoded;
        }

        $saasPath = $this->agentDir . '/upmvc-saas-pack.json';
        if (is_file($saasPath)) {
            $decoded = json_decode((string) file_get_contents($saasPath), true);
            if (!is_array($decoded)) {
                $this->err("Invalid JSON: {$saasPath}");
                return false;
            }
            $this->saasPack = $decoded;
        }

        return true;
    }

    private function hasSaasPackFile(): bool
    {
        return $this->saasPack !== [];
    }

    /** @return array<string, mixed> */
    private function scanProject(): array
    {
        $modulesPath = $this->appRoot . '/src/Modules';
        $modules = [];

        if (is_dir($modulesPath)) {
            foreach (scandir($modulesPath) ?: [] as $entry) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }
                $moduleDir = $modulesPath . '/' . $entry;
                if (!is_dir($moduleDir)) {
                    continue;
                }
                $hasRoutes = is_file($moduleDir . '/Routes/Routes.php');
                $modules[] = [
                    'name' => $entry,
                    'has_routes' => $hasRoutes,
                ];
            }
        }

        usort($modules, static fn(array $a, array $b): int => strcmp($a['name'], $b['name']));

        $packagesFile = $this->appRoot . '/src/Etc/packages.php';
        $providers = [];
        if (is_file($packagesFile)) {
            $loaded = require $packagesFile;
            if (is_array($loaded)) {
                $providers = array_values(array_filter($loaded, 'is_string'));
            }
        }

        $composer = $this->readComposer();
        $isSaas = $this->detectSaas($composer, $providers);
        $includeSaasPack = $isSaas && $this->hasSaasPackFile();

        return [
            'app_root' => $this->appRoot,
            'framework' => 'upMVC',
            'has_env' => is_file($this->appRoot . '/src/Etc/.env'),
            'has_env_example' => is_file($this->appRoot . '/src/Etc/.env.example'),
            'env_path' => 'src/Etc/.env',
            'has_env_legacy_root' => is_file($this->appRoot . '/.env'),
            'modules' => $modules,
            'module_count' => count($modules),
            'packages_php' => is_file($packagesFile),
            'providers' => $providers,
            'composer_require' => $composer['require'] ?? [],
            'is_saas' => $isSaas,
            'has_saas_pack_file' => $this->hasSaasPackFile(),
            'include_saas_pack' => $includeSaasPack,
            'agent_pack_version' => $this->knowledge['meta']['version'] ?? 'unknown',
        ];
    }

    /** @return array<string, mixed> */
    private function readComposer(): array
    {
        $path = $this->appRoot . '/composer.json';
        if (!is_file($path)) {
            return [];
        }

        $decoded = json_decode((string) file_get_contents($path), true);
        return is_array($decoded) ? $decoded : [];
    }

    /** @param array<string, mixed> $composer */
    /** @param list<string> $providers */
    private function detectSaas(array $composer, array $providers): bool
    {
        $require = $composer['require'] ?? [];
        if (is_array($require)) {
            foreach (array_keys($require) as $pkg) {
                if (stripos((string) $pkg, 'upmvc-saas-pack') !== false) {
                    return true;
                }
            }
        }

        foreach ($providers as $provider) {
            if (stripos($provider, 'Saas') !== false || stripos($provider, 'UpmvcSaas') !== false) {
                return true;
            }
        }

        return false;
    }

    /** @param array<string, mixed> $scan */
    private function banner(array $scan): void
    {
        $this->out('');
        $this->out('  upMVC — you are already inside the house.');
        $this->out('  ─────────────────────────────────────────');
        $this->out('  Project:  ' . $scan['app_root']);
        $this->out('  Modules:  ' . $scan['module_count'] . ' in src/Modules/');
        if ($scan['is_saas']) {
            $this->out('  Mode:     SaaS (pack detected)');
        } else {
            $this->out('  Mode:     Standalone upMVC');
        }
        if (!$scan['has_env']) {
            $this->out('  Note:     src/Etc/.env not found — copy from src/Etc/.env.example');
            if ($scan['has_env_legacy_root'] ?? false) {
                $this->out('  Note:     .env at project root is not loaded — move/copy to src/Etc/.env');
            }
        }
        $this->out('');
    }

    private function askGoal(): string
    {
        $this->out('What do you want to do? (one line — examples below)');
        $this->out('  • Add a contact form module');
        $this->out('  • Bookings API for my SaaS tenants');
        $this->out('  • Audit why my .env settings are ignored');
        $this->out('  • Fix JWT login on /api/auth');
        $this->out('');
        $this->out('Your goal: ', false);

        $line = fgets(STDIN);
        $goal = is_string($line) ? trim($line) : '';

        if ($goal === '') {
            $goal = 'Explore the project and suggest the highest-value next step';
        }

        return $goal;
    }

    /** @return array{id: string, label: string}|null */
    private function matchWorkflow(string $goal): ?array
    {
        $lower = strtolower($goal);
        $workflows = $this->workflows['workflows'] ?? [];

        $best = null;
        $bestScore = 0;

        foreach ($workflows as $id => $wf) {
            if (!is_array($wf)) {
                continue;
            }
            $keywords = $wf['intent_keywords'] ?? [];
            $score = 0;
            foreach ($keywords as $kw) {
                if (is_string($kw) && str_contains($lower, strtolower($kw))) {
                    $score++;
                }
            }
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = ['id' => (string) $id, 'label' => (string) ($wf['description'] ?? $id)];
            }
        }

        return $best;
    }

    /** @param array<string, mixed> $scan */
    /** @param array{id: string, label: string}|null $workflow */
    /** @return array<string, mixed> */
    private function buildSession(array $scan, string $goal, ?array $workflow, bool $includeScaffolds): array
    {
        $wfId = $workflow['id'] ?? 'explore';
        $wfData = $this->workflows['workflows'][$wfId] ?? null;

        return [
            'generated_at' => date('c'),
            'goal' => $goal,
            'workflow' => $wfId,
            'workflow_steps' => is_array($wfData) ? ($wfData['steps'] ?? []) : [],
            'scan' => $scan,
            'include_saas_pack' => (bool) ($scan['include_saas_pack'] ?? false),
            'include_scaffolds' => $includeScaffolds,
            'agent_files' => [
                'knowledge' => 'docs/agent/upmvc-knowledge.json',
                'rules' => 'docs/agent/upmvc-rules.json',
                'workflows' => 'docs/agent/upmvc-workflows.json',
                'scaffolds' => $includeScaffolds ? 'docs/agent/upmvc-scaffolds.json' : null,
                'saas' => ($scan['include_saas_pack'] ?? false)
                    ? 'docs/agent/upmvc-saas-pack.json'
                    : null,
            ],
            'scaffold_types' => $includeScaffolds
                ? array_keys($this->scaffolds['module_types'] ?? [])
                : [],
            'rules_must' => array_slice($this->rules['must'] ?? [], 0, 8),
            'rules_never' => array_slice($this->rules['never'] ?? [], 0, 8),
        ];
    }

    /** @param array<string, mixed> $session */
    private function buildPrompt(array $session): string
    {
        $lines = [];
        $lines[] = '# upMVC Agent Session';
        $lines[] = '';
        $lines[] = 'You are inside an upMVC project. **Do not ask the user to explain the framework.**';
        $lines[] = '';
        $lines[] = '## User goal';
        $lines[] = $session['goal'];
        $lines[] = '';
        $lines[] = '## Project snapshot';
        $scan = $session['scan'];
        $lines[] = '- App root: `' . $scan['app_root'] . '`';
        $lines[] = '- Modules (' . $scan['module_count'] . '): ' . $this->formatModuleList($scan['modules']);
        $lines[] = '- SaaS mode: ' . ($scan['is_saas'] ? 'yes' : 'no');
        if (!empty($scan['providers'])) {
            $lines[] = '- Providers: ' . implode(', ', $scan['providers']);
        }
        $lines[] = '';
        $lines[] = '## Workflow: `' . $session['workflow'] . '`';
        foreach ($session['workflow_steps'] as $step) {
            if (is_string($step)) {
                $lines[] = '- ' . $step;
            }
        }
        $lines[] = '';
        $lines[] = '## Load context (required)';
        foreach ($session['agent_files'] as $key => $path) {
            if ($path !== null) {
                $lines[] = '- `' . $path . '`';
            }
        }
        $lines[] = '';
        $lines[] = '## Rules (summary — full list in upmvc-rules.json)';
        $lines[] = '**Must:**';
        foreach ($session['rules_must'] as $rule) {
            $lines[] = '- ' . $rule;
        }
        $lines[] = '';
        $lines[] = '**Never:**';
        foreach ($session['rules_never'] as $rule) {
            $lines[] = '- ' . $rule;
        }
        if ($session['include_saas_pack']) {
            $lines[] = '';
            $lines[] = '## SaaS';
            $lines[] = 'This project uses the SaaS pack. All tenant data queries must filter by `tenant_id`.';
            $lines[] = 'Use `docs/agent/upmvc-saas-pack.json` for middleware and module patterns.';
        }
        if ($session['include_scaffolds'] ?? false) {
            $lines[] = '';
            $lines[] = '## Module scaffolds (opt-in)';
            $lines[] = 'Load `docs/agent/upmvc-scaffolds.json` for module types, field schema, and route patterns.';
            if (!empty($session['scaffold_types'])) {
                $lines[] = 'Available types: `' . implode('`, `', $session['scaffold_types']) . '`';
            }
            $lines[] = 'Follow scaffold_steps in that file; output a JSON plan before creating files.';
        }
        $lines[] = '';
        $lines[] = '## Your task';
        $lines[] = '1. Confirm understanding in 2–3 sentences.';
        $lines[] = '2. Output a JSON **plan** (steps, files_touched, config_changes, risks) — wait for approval before editing.';
        $lines[] = '3. Match existing patterns in `src/Modules/` before inventing new ones.';
        $lines[] = '';

        return implode("\n", $lines);
    }

    /** @param list<array{name: string, has_routes: bool}> $modules */
    private function formatModuleList(array $modules): string
    {
        if ($modules === []) {
            return '(none yet)';
        }

        $names = array_map(static fn(array $m): string => $m['name'], $modules);
        $preview = array_slice($names, 0, 12);
        $text = implode(', ', $preview);
        if (count($names) > 12) {
            $text .= ', ...';
        }
        return $text;
    }

    /** @param array<string, mixed> $session */
    private function writeOutput(array $session, string $prompt): bool
    {
        if (!is_dir($this->generatedDir) && !mkdir($this->generatedDir, 0755, true) && !is_dir($this->generatedDir)) {
            $this->err('Could not create: ' . $this->generatedDir);
            return false;
        }

        $promptPath = $this->generatedDir . '/last-prompt.md';
        $sessionPath = $this->generatedDir . '/last-session.json';

        if (file_put_contents($promptPath, $prompt) === false) {
            $this->err('Failed to write: ' . $promptPath);
            return false;
        }

        $json = json_encode($session, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false || file_put_contents($sessionPath, $json) === false) {
            $this->err('Failed to write: ' . $sessionPath);
            return false;
        }

        $this->out('Written:');
        $this->out('  ' . $promptPath);
        $this->out('  ' . $sessionPath);
        return true;
    }

    private function printDone(): void
    {
        $this->out('');
        $this->out('Paste last-prompt.md into Cursor or your agent chat.');
        $this->out('Or re-run with: php src/Tools/upmvc-next.php --stdout');
        $this->out('Module scaffolds (optional): php src/Tools/upmvc-next.php --scaffold');
        $this->out('');
    }

    private function out(string $text, bool $newline = true): void
    {
        echo $text . ($newline ? PHP_EOL : '');
    }

    private function err(string $text): void
    {
        fwrite(STDERR, $text . PHP_EOL);
    }
}

exit((new UpmvcNext())->run($argv));
