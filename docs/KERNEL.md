# upMVC Kernel

The **product** is `src/Etc/` (plus `public/index.php` and the Composer package boundary).  
Modules under `src/Modules/` are optional. Demos are a Releases zip. Do not infer kernel limits from demos.

Verified against **2.5.0** (Thin Core). Claims below are from running code in `src/Etc/`, not from marketing copy.

---

## What is in / out

| In (kernel) | Out (not kernel) |
|-------------|------------------|
| Boot, config, router, middleware, security helpers | Auth / Test / React / Admin demos |
| `Application` package hooks | Welcome UI (app-owned homepage module) |
| Cache manager, JWT service, events, helpers | Anything under `src/Modules/*` except what *you* add |
| Tools: `doctor`, `migrate`, `cache-cli`, agent CLI | Maintainer-only zip builders (local / gitignored) |

`src/Common/` is shared base classes for *apps* (BaseModel, BaseView, …). Useful with the kernel; not the kernel’s public “framework API” in the same sense as `Router` / `Application`.

---

## Boot (entry → dispatch)

```
public/index.php
  define UPMVC_APP_ROOT
  require vendor/autoload.php
  new Start()                    → ConfigManager::load(), ErrorHandler, parse route
  Start::upMVC()
    new Router()
    HelperFacade::setRouter()
    Application::registerProviders()     ← packages.php
    global middleware (logging, optional CORS, AuthMiddleware)
    named middleware (csrf, rate_limit, jwt, auth, cors, …)
    Application::bootProviders($router)
    Routes::startRoutes()
      for each Application::getModulePathsForRoutes():
        InitModsImproved::addRoutes()    ← discover + custom-routes.php last
      Router::dispatch(...)
```

**Honesty notes (boot)**

- Boot does **not** open a database. PDO opens when something constructs `Database` / `BaseModel`. Welcome needs no DB.
- `Start::$defaultProtectedRoutes` still lists demo-ish prefixes (`/admin/*`, `/moda`, …). Override with `PROTECTED_ROUTES` in `.env` or `Application::addProtectedRoutes()`. Cleaning those defaults is a future kernel honesty fix — not done in this doc-only pass.
- Library mode: define `UPMVC_APP_ROOT` *before* autoload so `Application` and `Environment` resolve the **host** app paths (`.env`, `packages.php`, `src/Modules`).

---

## Config surface

| Layer | Role |
|-------|------|
| `src/Etc/.env` via `Environment::get()` | Canonical runtime values (`Application::path('src/Etc/.env')`) |
| `ConfigManager::get('dot.key')` | Structured app / database / cache / session / security / mail / logging |
| `Config.php` | Legacy URL helpers (`getReqRoute`, `BASE_URL` / site path fallbacks) |
| `ConfigDatabase.php` | DB fallbacks when env incomplete (dev-oriented) |

**Read config with** `Environment::get()` or `ConfigManager::get()` — not `$_ENV` for `.env` keys (`putenv` does not populate `$_ENV`).

**Required-ish env:** `DOMAIN_NAME`, `SITE_PATH`, `APP_ENV`. Use `APP_DEBUG` for error display. Validate warnings are logged; they do not always hard-stop boot.

See also: `docs/CORE_AREAS_AND_CONFIGURATION.md`, `docs/CONFIGURATION_FALLBACKS.md`.

---

## Router surface

### Registration APIs (real)

```php
$router->addRoute($path, $class, $method, array $middleware = [], array $methods = []);
// returns void — do NOT chain ->name() on addRoute

$router->addParamRoute($pattern, $class, $method, $middleware = [], $constraints = [], $methods = []);
// returns $this — ->name() is valid here
```

There is **no** `$router->get()`, `->group()`, or `->middleware()` fluent group API. Named middleware is the 4th argument array on `addRoute` / `addParamRoute`.

### Discovery order

1. Package / extra module paths first (`Application::getModulePathsForRoutes()` — app `src/Modules` scanned **last** so local routes can override packs).
2. Per path: `InitModsImproved` finds `*/Routes/Routes.php` (and nested `*/Modules/*/Routes/Routes.php` when enabled).
3. Then `src/Etc/custom-routes.php` — each entry `addRoute`s; **same path overwrites**. Stock `/` → `Welcome\Controller`.

Modules without `Routes/Routes.php` are invisible to discovery (Welcome is intentional: homepage only via `custom-routes.php`).

### Dispatch contract

Controllers are invoked with **`(string $route, string $method)`** (plus original URI available to middleware). Methods that need extra required args are not router-compatible.

HTTP status: missing route → 404; wrong method → 405 + `Allow` (guarded by integration tests).

---

## Package / provider surface

Optional `src/Etc/packages.php`:

```php
return [ \Vendor\Package\ServiceProvider::class, ... ];
```

| Hook | When | Purpose |
|------|------|---------|
| `registerProviders()` | Before middleware | Paths, migrations, protected routes, factories |
| `bootProviders($router)` | After named middleware | Wire routes that need the router |

`Application` APIs: `path()`, `addModulePath()`, `addMigrationPath()`, `addProtectedRoutes()`, `getModulePathsForRoutes()`.

SaaS pack (`bitshost/upmvc-saas-pack`) is the primary interoperability stress test — kernel changes must not silently break it.

---

## Kernel tools (CLI)

| Tool | Role |
|------|------|
| `php src/Tools/doctor.php` | Report modules discovery cannot see |
| `php src/Tools/migrate.php` | Apply migrations; regenerate `database/schema.sql` |
| `php src/Tools/cache-cli.php` | Cache stores / module discovery cache |
| `php src/Tools/upmvc-next.php` | Agent prompt pack (not runtime) |

---

## Short audit — boot / config / router only (2.5.0)

| Area | Finding | Severity | Next step (later) |
|------|---------|----------|-------------------|
| Boot | No DB on boot — Welcome-safe | OK | Keep |
| Boot | Default protected routes smell like demos | Low | Slim defaults to `[]` or documented stubs |
| Config | `.env` via `Application::path` — correct for library mode | OK | Keep documenting `UPMVC_APP_ROOT` |
| Config | Dual legacy (`Config` / `ConfigDatabase`) still in play | Medium | Prefer ConfigManager; deprecate gradually |
| Router | `custom-routes` overwrite is the homepage contract | OK | Treat as public |
| Router | Discovery requires `Routes/Routes.php` | OK | Document; `doctor` already explains misses |
| Router | `addRoute` void vs `addParamRoute` chainable | OK | Already corrected in routing docs |
| Packages | Path order: packs then app | OK | SaaS regression = CI / manual smoke |

No code changes in this pass — documentation + agent knowledge only.

---

## Agent entry

- Facts: `docs/agent/upmvc-knowledge.json` → `kernel_surface`
- Rules: `docs/agent/upmvc-rules.json`
- This file: load for any kernel-focused session (`AGENTS.md`)
