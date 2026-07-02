# upMVC Developer Tools

CLI utilities for agent-assisted development and cache maintenance. **Module scaffolding is handled by the AI agent pack** (optional `upmvc-scaffolds.json`), not PHP generators.

## Agent prompt builder

```bash
php src/Tools/upmvc-next.php
php src/Tools/upmvc-next.php --goal "Add a Blog CRUD module"
php src/Tools/upmvc-next.php --scaffold --goal "Scaffold Api/Bookings module"
php src/Tools/upmvc-next.php --stdout
```

- Default: general agent context (`knowledge`, `rules`, `workflows`)
- `--scaffold`: also include optional module scaffold pack in the prompt
- SaaS pack included automatically when SaaS project detected

See [docs/AGENT_PACK.md](../../docs/AGENT_PACK.md).

## Cache maintenance

```bash
php src/Tools/cache-cli.php list
php src/Tools/cache-cli.php stats
php src/Tools/cache-cli.php clear:modules
php src/Tools/cache-cli.php clear:admin
php src/Tools/cache-cli.php clear:all
```

Clears module discovery cache (`InitModsImproved`), Admin dynamic route cache, and `CacheManager` stores.

## Removed (v2.3.7+)

Legacy PHP module generators (`createmodule/`, `modulegenerator/`, `crudgenerator/`, `ModuleGeneratorEnhanced/`) were removed. Module patterns live in `docs/agent/upmvc-scaffolds.json` — opt-in only.
