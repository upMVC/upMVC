# Agent pack files (this folder)

**Full guide:** [../AGENT_PACK.md](../AGENT_PACK.md) — what it is, how to use it, how to update rules.

## Quick start

```bash
php src/Tools/upmvc-next.php
```

Paste `generated/last-prompt.md` into your agent chat.

## Files here

| File | Purpose |
|------|---------|
| `upmvc-knowledge.json` | Framework facts |
| `upmvc-rules.json` | Must / never rules |
| `upmvc-workflows.json` | Intent → recipe mapping |
| `upmvc-scaffolds.json` | Module builder (optional — `--scaffold`) |
| `upmvc-saas-pack.json` | SaaS pack (when applicable) |
| `generated/` | CLI output (gitignored) |

## Updating rules

Edit `upmvc-rules.json` or `upmvc-workflows.json`, bump `meta.version`, run `vendor\bin\phpunit tests\Unit\Tools`. See the [main guide](../AGENT_PACK.md#how-to-update-rules).
