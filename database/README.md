# Database

Everything upMVC needs to create its schema. Pick whichever route suits you.

## Option A — quick start (no tooling)

```bash
mysql -u root -p your_db < database/schema.sql
mysql -u root -p your_db < database/seed.sql      # optional demo data
```

Fastest way to get running, and the easiest way to read the data model
without installing anything.

## Option B — migrations (recommended for real projects)

```bash
php src/Tools/migrate.php --status    # what's applied, what's pending
php src/Tools/migrate.php             # apply everything pending
```

Applied migrations are recorded in a `migrations` table, so re-running is
safe and future schema changes apply cleanly to an existing database.
Credentials come from `src/Etc/.env` — the same file the app uses.

`migrate.php` also rewrites `database/schema.sql` after every run, so the
snapshot never drifts from the migrations.

Other flags:

| Flag | Effect |
|---|---|
| `--status` | list applied / pending / missing |
| `--dump` | regenerate `database/schema.sql` only |
| `--baseline` | record migrations as applied **without running them** |
| `--fresh` | drop the tracking table and re-run everything — **dev only** |
| `--root=/path` | set the app root explicitly (auto-detected otherwise) |

## Started with Option A, want migrations later?

If you built the database by importing `schema.sql`, the tracking table is
empty — so a plain `migrate.php` would try to replay every migration from
the beginning. Mark the current set as applied first:

```bash
php src/Tools/migrate.php --baseline
```

Nothing is executed; the migrations are just recorded. From then on only
genuinely new ones run. (Baselining an empty database is refused — that would
record a schema as created when it never was.)

## Write migrations to be re-runnable

MySQL and MariaDB **commit DDL implicitly** — `CREATE`, `ALTER` and `DROP`
cannot be rolled back. So if a migration contains several statements and one
fails partway, the earlier ones are already applied and there is no undo. The
runner does not wrap migrations in a transaction, because doing so would look
like protection while providing none.

The practical consequence: a failed migration is not recorded, so the next run
replays the **whole file**. Write them so that is harmless.

| Prefer | Over |
|---|---|
| `CREATE TABLE IF NOT EXISTS foo` | `CREATE TABLE foo` |
| `INSERT IGNORE` / `ON DUPLICATE KEY UPDATE` | bare `INSERT` |
| `DROP TABLE IF EXISTS foo` | `DROP TABLE foo` |

`ALTER TABLE ... ADD COLUMN` has no `IF NOT EXISTS` in MySQL, so it is the one
to keep alone in its own migration — then a failure has nothing before it to
half-apply.

If a migration does fail, `migrate.php` prints exactly what state you are in
and how to recover.

## Files

| File | Hand-written? | What |
|---|---|---|
| `migrations/001_base_schema.sql` | yes | `users` — the source of truth |
| `schema.sql` | **no — generated** | full current structure |
| `seed.sql` | yes | demo users |
| `demo-modules.sql` | yes | tables the bundled demo modules need |

**Never edit `schema.sql` by hand.** It's produced by `--dump`; edits are
overwritten on the next migrate. Change the schema by adding a migration.

## Demo logins (from `seed.sql`)

| User | Password | Role | State |
|---|---|---|---|
| `admin` | `Admin5678!` | `platform_admin` | active |
| `demo` | `Test1234!` | `tenant_user` | active |
| `ghost` | `Test1234!` | `tenant_user` | inactive — for testing activation |

Demo credentials. Never load `seed.sql` into production.

## Multi-tenant SaaS

The kernel ships only `users`. The `tenants`, `plans` and `refresh_tokens`
tables belong to **[bitshost/upmvc-saas-pack](https://github.com/BitsHost/upMVC-SaaS-Pack)**,
which registers its own migration path from its service provider — so
`migrate.php` picks them up automatically once the pack is installed.

Run order is **kernel → packages → app**: the pack's `refresh_tokens`
declares a foreign key to the kernel's `users`.

The pack's `database/` folder mirrors this one — `migrations/`, a generated
`schema.sql`, and a `seed.sql` with a fully populated multi-tenant demo
environment (5 tenants covering active / trial / suspended and plan gating).
