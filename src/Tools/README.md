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

## Doctor — why isn't my module loading?

```bash
php src/Tools/doctor.php            # check every registered module path
php src/Tools/doctor.php --quiet    # only print problems (silent when clean)
php src/Tools/doctor.php --strict   # ignore .doctorignore, report everything
```

Module discovery is a filesystem scan, so a module that breaks the naming rules
is skipped **silently** — no error, no log line, just a 404 that looks like a
mistyped URL. Run this when a route 404s and you cannot see why.

It reports four distinct failures, each with the specific fix:

| Detected | Meaning |
|---|---|
| filename isn't `Routes.php` | glob matches that exact name only |
| folder isn't `Routes/` | lowercase `routes/` loads on Windows/macOS, silently vanishes on Linux |
| nesting without a literal `Modules/` segment | `Api/Billing/` is not discovered; `Api/Modules/Billing/` is |
| namespace or method mismatch | file is found but the class won't autoload, or has no `routes()` |

Exit codes: `0` clean, `1` problems found, `2` bad usage — so it can gate CI.

### Accepting a known finding

Put it in `.doctorignore` at the app root — one repo-relative path or glob per
line, `#` for comments:

```
# glob
src/Modules/Legacy*/*
# exact path
src/Modules/Mail/Routes/MailRoutes.php
```

**Ignored findings are still printed**, under a separate heading, with the
pattern that matched them. They just don't affect the exit code. Suppressing
them from view would recreate the silent-skip problem this tool exists to
expose — so the rule is: quiet in CI, never invisible.

The shipped `.doctorignore` contains one entry, `Mail/Routes/MailRoutes.php`,
with the reasoning written next to it. Write a reason for anything you add; an
entry with no explanation is indistinguishable from a bug someone silenced.

This is deliberately a CLI check and **not** a runtime warning. Absence is not
an error: a module that doesn't exist has no routes, and warning about things
that aren't there is noise. What it reports is narrower — a `Routes/` directory
that exists, holds PHP, and was walked past anyway.

## Removed (v2.3.7+)

Legacy PHP module generators (`createmodule/`, `modulegenerator/`, `crudgenerator/`, `ModuleGeneratorEnhanced/`) were removed. Module patterns live in `docs/agent/upmvc-scaffolds.json` — opt-in only.
