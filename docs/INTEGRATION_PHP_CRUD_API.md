# Integration Guide: upMVC + PHP CRUD API Generator

Combine **upMVC** (app / modules / SPA shells) with **[PHP CRUD API Generator](https://github.com/BitsHost/PHP-CRUD-API-Generator)** (data plane) for a full-stack PHP setup:

- Server-rendered pages and module routes (upMVC)
- Table CRUD REST API — zero codegen (PHP CRUD API Generator)
- Shared database; optional shared JWT secret
- React/Vue **islands** or SPA entrypoints that `fetch` the API
- Mobile / third-party clients on the same API

upMVC **2.5+ (Thin Core):** stock tree is kernel + **Welcome** only. Optional UI demos come from the Releases zip. The API is a separate Composer package under `public/api/` (or your own Alias).

---

## Roles (read this first)

| Piece | Job |
|--------|-----|
| **PHP CRUD API Generator** | Data plane — list/read/create/update/delete, filters, RBAC, rate limits |
| **upMVC** | App plane — boot, modules, SSR, Auth UI, SPA/island entrypoints |
| **JS in a module** | Orchestration, joins, workflows — not the API’s job |

Do not reinvent per-table CRUD controllers in upMVC when the generator already exposes the tables.

---

## Architecture (Thin Core paths)

Document root must be **`public/`** (`php -S … -t public` or vhost). Put the API **under `public/api/`** so one server reaches both.

```
myproject/                          ← UPMVC_APP_ROOT
├── public/
│   ├── index.php                   → upMVC entry
│   ├── .htaccess
│   └── api/                        → PHP CRUD API Generator
│       ├── index.php               → API entry
│       ├── dashboard.html          → protect in production
│       ├── health.php              → protect in production
│       ├── config/                 → app-owned copies (recommended)
│       │   ├── db.php              → same DB as upMVC
│       │   └── api.php             → JWT, RBAC, rate limit
│       └── vendor/                 → bitshost/php-crud-api-generator
├── src/
│   ├── Etc/
│   │   ├── .env                    → DOMAIN_NAME, SITE_PATH, DB_*, JWT_SECRET
│   │   └── custom-routes.php       → '/' → Welcome (change anytime)
│   └── Modules/
│       ├── Welcome/                → ships with create-project (no DB)
│       ├── Auth/                   → optional (demos zip or your own)
│       └── React…/                 → SPA / islands that call /api/
└── composer.json                   → bitshost/upmvc
```

Alternative: keep `api/` at the project root and Apache-`Alias` it — fine for production; for the PHP built-in server, **`public/api/` is simpler**.

---

## Installation

### 1. upMVC

```bash
composer create-project bitshost/upmvc myproject
cd myproject

cp src/Etc/.env.example src/Etc/.env
# Edit src/Etc/.env:
#   DOMAIN_NAME=http://localhost
#   SITE_PATH=                    # empty if docroot is public/ at domain root
#   # or SITE_PATH=/myproject/public when using a subfolder
#   DB_HOST=127.0.0.1
#   DB_NAME=myproject_db
#   DB_USER=root
#   DB_PASS=
#   JWT_SECRET=…                  # same value you will put in api config (optional)
```

Welcome at `/` needs **no** database. Create/import DB when modules or the API need tables.

Optional demos (Auth, Test, React, …): download `upmvc-demos.zip` from [Releases](https://github.com/upMVC/upMVC/releases), paste into `src/Modules/`, import `demo-modules.sql` if needed. See [Module Philosophy](MODULE_PHILOSOPHY.md).

### 2. API under `public/api`

```bash
mkdir public/api
cd public/api

composer require bitshost/php-crud-api-generator

# Entry + ops UI (from the package)
cp vendor/bitshost/php-crud-api-generator/public/index.php index.php
cp vendor/bitshost/php-crud-api-generator/dashboard.html dashboard.html
cp vendor/bitshost/php-crud-api-generator/health.php health.php

# Prefer app-owned config (do not edit only inside vendor long-term)
mkdir -p config
cp vendor/bitshost/php-crud-api-generator/config/db.php config/db.php
cp vendor/bitshost/php-crud-api-generator/config/api.php config/api.php
```

Point `public/api/index.php` at **your** `config/` copies (adjust the package’s require paths if the stock `index.php` loads vendor configs).

### 3. Same database as upMVC

`public/api/config/db.php` — use the **same** credentials as `src/Etc/.env`:

```php
<?php
return [
    'host' => '127.0.0.1',
    'dbname' => 'myproject_db',
    'user' => 'root',
    'pass' => '',
    'charset' => 'utf8mb4',
];
```

### 4. JWT (optional, recommended for SPA/mobile)

Align secrets with upMVC’s `.env` `JWT_SECRET` (read via `Environment::get('JWT_SECRET')` / ConfigManager — **not** a hardcoded `define` in `Config.php`).

```php
// public/api/config/api.php (shape may vary by generator version — see package docs)
return [
    'auth_enabled' => true,
    'auth_method' => 'jwt',
    'jwt_secret' => 'same-value-as-JWT_SECRET-in-src-Etc-env',
    // … issuer, audience, RBAC, rate_limit — see generator CONFIGURATION.md
];
```

Protect `dashboard.html` and `health.php` before production (IP allowlist / auth). See the generator’s dashboard security docs.

---

## Run locally

```bash
cd myproject
php -S localhost:8080 -t public
```

| URL | What |
|-----|------|
| `http://localhost:8080/` | Welcome (upMVC) |
| `http://localhost:8080/auth` | Auth module if installed |
| `http://localhost:8080/api/index.php?action=tables` | API |
| `http://localhost:8080/api/dashboard.html` | API monitor (dev only) |

If `SITE_PATH` is a subfolder, prefix URLs accordingly.

---

## Usage sketches

### Login UI in upMVC, data via API

Session/login stays in an Auth (or SaaS) module. SPA code stores a JWT from the API `login` action (or a small upMVC endpoint that mints one with the shared secret).

```bash
curl -X POST -d "username=admin&password=…" \
  "http://localhost:8080/api/index.php?action=login"

curl -H "Authorization: Bearer YOUR_TOKEN" \
  "http://localhost:8080/api/index.php?action=list&table=products"
```

### Island / SPA module calling the API

```javascript
// e.g. src/Modules/React/… or your own module assets
fetch('/api/index.php?action=list&table=products', {
  headers: { Authorization: 'Bearer ' + localStorage.getItem('jwt_token') }
})
  .then((res) => res.json())
  .then((payload) => { /* render */ });
```

Use paths relative to the site (respect `SITE_PATH` / `BASE_URL` when building asset URLs).

### Shared Auth note

Prefer **PDO + `password_verify`** against your `users` table (upMVC style). Do not treat RedBean examples as required — RedBean is optional (`Userorm` demo only).

---

## Security checklist

1. **Same DB credentials** in `src/Etc/.env` and `public/api/config/db.php` (or a tighter DB user for the API).
2. **Same `JWT_SECRET`** when both sides validate JWTs.
3. **CORS** only if the SPA is on another origin — configure in the API package; upMVC has `CORS_*` in `.env` for its own responses.
4. **Rate limit + table allow/deny** on the API; don’t expose ops HTML publicly.
5. **Never return password hashes** from upMVC JSON endpoints or the API field lists.

---

## Frontend patterns

### 1. Islands

upMVC renders the page shell; a React/Vue island on that page calls `/api/…`.

### 2. Hybrid

upMVC SSR for first paint / SEO; client hydrates and refreshes from the API.

### 3. Static SPA under public

```
public/app/     → built SPA
public/api/     → data plane
public/index.php → optional marketing / Welcome / Auth
```

---

## When to use which stack

| Goal | Start with |
|------|------------|
| Modular PHP + optional SPA shells + table API | **upMVC + this API** |
| Multi-tenant product | **upMVC-SaaS** (+ API only if you still want a raw data plane with tenant-aware policy) |
| API only, no HTML app | Generator alone |
| Tiny CRUD UI, few tables | upMVC modules alone |

---

## Resources

### upMVC
- [README](../README.md) — Thin Core, demos zip, `src/Etc/.env`
- [KERNEL.md](KERNEL.md) — boot / config / router contract
- [Module Philosophy](MODULE_PHILOSOPHY.md)
- [React / Islands](REACT_INTEGRATION_PATTERNS.md) · [Islands index](ISLANDS_ARCHITECTURE_INDEX.md)

### PHP CRUD API Generator
- [README](https://github.com/BitsHost/PHP-CRUD-API-Generator/blob/main/README.md)
- [Quick start](https://github.com/BitsHost/PHP-CRUD-API-Generator/blob/main/docs/QUICK_START.md)
- [Authentication](https://github.com/BitsHost/PHP-CRUD-API-Generator/blob/main/docs/AUTHENTICATION.md)
- [Configuration](https://github.com/BitsHost/PHP-CRUD-API-Generator/blob/main/docs/CONFIGURATION.md)

---

**Help:** [upMVC issues](https://github.com/upMVC/upMVC/issues) · [API Generator issues](https://github.com/BitsHost/PHP-CRUD-API-Generator/issues)

BitsHost / upMVC
