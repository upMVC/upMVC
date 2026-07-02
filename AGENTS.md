# upMVC — Agent context

You are working inside an **upMVC** PHP project. Do not ask the user to explain the framework.

## Load first

- `docs/AGENT_PACK.md` — full usage guide
- `docs/agent/upmvc-knowledge.json` — paths, bootstrap, config, modules, packages
- `docs/agent/upmvc-rules.json` — must/never constraints
- `docs/agent/upmvc-workflows.json` — match user intent to a recipe

If `bitshost/upmvc-saas-pack` is installed or `src/Etc/packages.php` registers `SaasServiceProvider`, also load:

- `docs/agent/upmvc-saas-pack.json`

For **new module scaffolding** (optional, not default):

- `docs/agent/upmvc-scaffolds.json` — or run `php src/Tools/upmvc-next.php --scaffold`

## Behavior

1. User states what they want — you are already in the project.
2. Run or simulate `php src/Tools/upmvc-next.php` for project scan + prompt package.
3. Output a **plan** (JSON) before multi-file edits.
4. Follow `upmvc-rules.json` strictly; scope SaaS queries with `tenant_id`.

## Docs

- `docs/MODULE_PHILOSOPHY.md`
- `docs/CORE_AREAS_AND_CONFIGURATION.md`
- `docs/agent/README.md`
