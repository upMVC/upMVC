# upMVC Agent Pack

Portable knowledge for AI coding assistants (Cursor, Claude, Gemini, local LLMs). The goal: **drop the user in the middle of the house** — they state what they want; the agent already knows upMVC paths, rules, and workflows.

This complements Cursor (or any IDE agent). It does not replace it.

---

## What it is

The agent pack is a small set of **versioned JSON files** plus a thin **CLI script** that:

1. Scans your project (modules, `.env`, `packages.php`, SaaS pack detection)
2. Asks what you want to build (one question)
3. Outputs a ready-to-paste prompt with context, workflow steps, and rule summaries

```
docs/agent/
  upmvc-knowledge.json   ← framework facts (bootstrap, config, modules, packages)
  upmvc-rules.json       ← hard must / never rules
  upmvc-workflows.json   ← recipes (create module, SaaS CRUD, config audit, …)
  upmvc-scaffolds.json   ← optional module builder pack (not default)
  upmvc-saas-pack.json   ← SaaS pack architecture (when applicable)
  generated/             ← ephemeral CLI output (gitignored)

src/Tools/upmvc-next.php  ← interactive prompt builder
AGENTS.md                 ← entry point for Cursor / cloud agents
```

---

## Quick start

From the project root:

```bash
php src/Tools/upmvc-next.php
```

You will see a short project snapshot, then:

```
What do you want to do?
  • Add a contact form module
  • Bookings API for my SaaS tenants
  • Audit why my .env settings are ignored
```

The script writes:

- `docs/agent/generated/last-prompt.md` — paste into your agent chat
- `docs/agent/generated/last-session.json` — machine-readable scan + workflow match

### Non-interactive

```bash
php src/Tools/upmvc-next.php --goal "Add a bookings API for my SaaS"
php src/Tools/upmvc-next.php --goal "Audit config wiring" --stdout
php src/Tools/upmvc-next.php --scaffold --goal "Create Blog CRUD module"
```

`--scaffold` includes optional `upmvc-scaffolds.json` (module types, field schema). Default sessions use general context only.

---

## Use in Cursor

### Option A — One-shot (simplest)

1. Run `php src/Tools/upmvc-next.php`
2. Open `docs/agent/generated/last-prompt.md`
3. Paste into a new Cursor chat

### Option B — Project rule (persistent)

Create or extend a Cursor rule that tells the agent to load:

- `docs/agent/upmvc-knowledge.json`
- `docs/agent/upmvc-rules.json`

Add `upmvc-saas-pack.json` only when your project uses the SaaS pack. The CLI treats this file as **optional** — standalone installs need only the three core JSON files.

### Option C — `AGENTS.md` (cloud agents)

Root [`AGENTS.md`](../AGENTS.md) points agents at this pack automatically when supported.

---

## Use in other agents

Import the JSON files into system context, a skill, or a RAG index:

| File | When to load |
|------|----------------|
| `upmvc-knowledge.json` | Always |
| `upmvc-rules.json` | Always |
| `upmvc-workflows.json` | When planning multi-step work |
| `upmvc-saas-pack.json` | Only for upMVC-SaaS / `bitshost/upmvc-saas-pack` projects |
| `upmvc-scaffolds.json` | Only when scaffolding new modules (`--scaffold` or user rule) |

Always pair **knowledge + rules** for core work. Scaffolds and SaaS packs are **opt-in extensions**.

---

## File reference

### `upmvc-knowledge.json`

Framework facts an agent should not re-discover each session:

- Bootstrap flow (`public/index.php` → `Start.php` → routing)
- Paths (`src/Etc/.env` — canonical runtime path via `Environment::load()`, not project-root `.env`)
- Config access (`Environment::get()`, `ConfigManager::get()`)
- Module structure and auto-discovery
- Package/provider system (`Application::addModulePath()`, `registerProviders()`)
- Middleware names and behavior

Bump `meta.version` when you change structure or add major sections.

### `upmvc-rules.json`

Hard constraints — **must**, **never**, **saas_must**, **prefer**:

```json
{
  "must": [
    "Read config with Environment::get() or ConfigManager::get() — never $_ENV for .env values"
  ],
  "never": [
    "Hardcode credentials, API keys, or secrets in PHP files"
  ]
}
```

These are the guardrails. Keep the list short and actionable.

### `upmvc-workflows.json`

Recipes keyed by intent (`create_module`, `saas_domain_module`, `config_audit`, …). Each workflow has `intent_keywords` and ordered `steps`. The CLI matches user goals to workflows by keyword overlap.

### `upmvc-scaffolds.json`

Optional module builder pack (replaces removed PHP generators). Module types (`basic`, `api`, `crud`, `auth`, `dashboard`, `submodule`, `saas_api`), CRUD field schema, route patterns. **Not loaded by default** — use `php src/Tools/upmvc-next.php --scaffold` or add to Cursor rules when you scaffold often.

### `upmvc-saas-pack.json`

SaaS-specific knowledge: three-layer architecture (kernel + pack + starter), `jwt` / `tenant` / `feature:*` middleware, `tenant_id` scoping, `SaasApiController` patterns. Load only when the SaaS pack is present.

---

## How to update rules

### Add a new must/never rule

1. Edit `docs/agent/upmvc-rules.json`
2. Add one clear sentence to `must`, `never`, or `saas_must`
3. If the rule needs context (why it exists), add a short note in `docs/AGENT_PACK.md` or the relevant framework doc — keep JSON entries terse
4. Run tests: `vendor\bin\phpunit tests\Unit\Tools`
5. Bump `meta.version` in `upmvc-rules.json` (patch: `1.0.0` → `1.0.1`)

**Good rule:** actionable, verifiable — *"Use PDO prepared statements for all SQL"*

**Bad rule:** vague — *"Write good code"*

### Add a new workflow

1. Edit `docs/agent/upmvc-workflows.json`
2. Add a workflow block:

```json
"my_workflow": {
  "intent_keywords": ["keyword1", "keyword2"],
  "steps": [
    "Step one",
    "Step two"
  ],
  "files_touched": ["src/Modules/{Name}/Routes/Routes.php"]
}
```

3. Test matching: `php src/Tools/upmvc-next.php --goal "your keyword phrase" --stdout`
4. Confirm `Workflow: \`my_workflow\`` appears in output

### Update framework facts

When bootstrap, config keys, or package APIs change in upMVC core:

1. Update `upmvc-knowledge.json` (and `upmvc-saas-pack.json` if SaaS-related)
2. Bump `meta.version`
3. Update this page if user-facing usage changed

### Team-only notes

Internal planning may live in `private-vault/` (gitignored). **Never** put credentials or secrets into the agent pack. Distill architecture lessons into the public JSON files.

---

## Standalone vs SaaS

| Signal | Mode |
|--------|------|
| No `bitshost/upmvc-saas-pack` in `composer.json` | Standalone — load 3 JSON files |
| `packages.php` registers `SaasServiceProvider` | SaaS — also load `upmvc-saas-pack.json` |

The CLI detects this automatically and includes SaaS rules in the generated prompt.

---

## Testing

Minimal smoke tests live in `tests/Unit/Tools/`:

```bash
vendor\bin\phpunit tests\Unit\Tools
```

Covers: JSON validity, `AGENTS.md` links, CLI exit codes, workflow routing, generated file output.

---

## Philosophy

- **No startup lecture** — user says what they want; agent already knows the house layout
- **JSON over custom platform** — portable, versionable, model-agnostic
- **Plan before write** — multi-file changes need an approved JSON plan first
- **Complement the IDE** — Cursor still edits code; this pack supplies upMVC-native context

---

## Related docs

- [Module Philosophy](MODULE_PHILOSOPHY.md)
- [Core Areas and Configuration](CORE_AREAS_AND_CONFIGURATION.md)
- [JWT Authentication](JWT_AUTHENTICATION.md)
- [docs/agent/README.md](agent/README.md) — folder quick reference
