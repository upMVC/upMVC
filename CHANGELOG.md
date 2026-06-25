# Changelog

## [Unreleased]

---

## v2.3.1 - Bootstrap Cleanup & Path Normalization (2026-06-25)

### Changed
- **All internal path resolution unified through `Application::getInstance()->path()`** — `ConfigManager`, `LoggingMiddleware`, `Security::rateLimitDir()`, and `FileCache` all previously derived their storage/log/cache paths independently using a mix of `THIS_DIR`, `dirname(__DIR__, N)`, or relative strings. All now call `Application::getInstance()->path()` so the single registered app root is the source of truth.
- **`LoggingMiddleware` default path is now absolute** — constructor previously defaulted to the relative string `'logs/requests.log'`, meaning the file would land wherever the process cwd happened to be. Default is now `Application::getInstance()->path('src/logs/requests.log')`.
- **`FileCache` cache path now matches `ConfigManager` advertised path** — `FileCache` was writing to a path derived from `dirname(__DIR__, 2)` which on some installs landed in `src/storage/cache` instead of `storage/cache`. Now explicitly uses `Application::getInstance()->path('storage/cache')`, matching the value advertised in `ConfigManager::loadAdditionalConfigs()`.
- **`ConfigManager` config file discovery fixed** — `loadConfigFiles()` previously used `defined('THIS_DIR') ? THIS_DIR : dirname(__DIR__, 3)`. `THIS_DIR` is defined in `Config::initConfig()` which runs after `ConfigManager::load()`, so the fallback always ran. Now uses `Application::getInstance()->path('src/Etc')` directly — correct regardless of call order.

### Fixed
- **`src/Etc/Cache/` source files now tracked by git** — `.gitignore` entry `cache/` was matching the `src/Etc/Cache/` namespace directory case-insensitively on Windows, silently excluding `CacheInterface.php`, `CacheManager.php`, and `FileCache.php` from version control. Added negation patterns `!src/Etc/Cache/` and `!src/Etc/Cache/**` to allow the source files through.
- **Dual error handler eliminated** — `Config.php` was registering a second static `App\Etc\ErrorHandler` after `Start.php` already registered the instance-based `App\Etc\Exceptions\ErrorHandler`. Both called `register_shutdown_function`, leaving two shutdown callbacks active per request. Consolidated to one: `Start.php` is the single registration point.
- **`LOG_PATH` now respected by the active handler** — log path resolution (`.env` `LOG_PATH`, absolute/relative detection) moved from `Config.php` to a new private `Start::resolveLogPath()`. The instance handler receives the correct path at bootstrap time.
- **`ErrorHandler` supports absolute paths** — constructor previously always prepended `$baseDir` to the log file argument, corrupting absolute paths on Windows and Unix. Now detects absolute paths and uses them as-is.
- **Session configuration guard is complete** — `session_name()` and `session_set_cookie_params()` were called before the `session_status()` check, potentially generating warnings on an already-active session. The entire session configuration block (name, cookie params, `session_start`) is now wrapped in a single `PHP_SESSION_NONE` guard.
- **Pointless `try/catch` removed** from `Start::upMVC()` — the block caught `\Exception` and immediately re-threw it with no handling, adding noise.
- **`public/index.php` requirement docs corrected** — stated PHP 8.0 (should be 8.1) and `.env` in `/etc` (should be `src/Etc`).

### Docs
- **`Config.php` STEP 4 note added** — documents that `$defaultProtectedRoutes` in `Start.php` exists for standalone demo modules and will be removed in a future release; apps and packages should register routes via `Application::addProtectedRoutes()`.
- **`Config.php` STEP 6/7 line references corrected** — comments pointed to `~line 95` / `~line 110`; updated to `~line 118` / `~line 126` after session block expansion.

---

## v2.3.0 - Package Architecture & Config Wiring (2026-06-25)

### Added
- **`Application.php` — runtime registry singleton** (`src/Etc/Application.php`). Resolves app root from `UPMVC_APP_ROOT` constant (library mode) or `dirname(__DIR__, 2)` (standalone). Exposes `addModulePath()`, `addMigrationPath()`, `addProtectedRoutes()`, `registerProviders()`, and `bootProviders()`. This is the core of the new package system.
- **Provider system** — `Start.php` now calls `$app->registerProviders()` before middleware setup and `$app->bootProviders($router)` after. Providers are loaded from `src/Etc/packages.php` (an array of class names). Each provider implements `register(Application $app)` for setup and `boot(Application $app, Router $router)` for runtime wiring (middleware, named factories).
- **`UPMVC_APP_ROOT` constant** defined in `public/index.php` before autoload — the single anchor point for path resolution in both standalone and library (Composer dependency) mode. Mirrors WordPress `ABSPATH`.
- **Middleware factory support in Router** — `Router::registerMiddlewareFactory(string $name, callable $factory)` enables parameterized middleware like `['feature:billing']` where the segment after `:` is passed to the factory. Used by `SaasServiceProvider` for plan-gating.
- **Module path ordering** — `Application::getModulePathsForRoutes()` moves the app's own `src/Modules/` to the end of the scan list so app routes override package routes when names collide.

### Changed
- **`composer.json` type stays `project`** — switched to `library` briefly but reverted; upMVC works as both a standalone project and a Composer dependency. The `UPMVC_APP_ROOT` constant is the mechanism that makes library mode work without changing the package type.
- **RedBean made optional** — removed `gabordemooij/redbean` from `require`; moved to `suggest`. Projects that need the ORM add it explicitly. Kernel no longer installs it transitively.
- **`Routes.php` scans all registered module paths** — previously only scanned `src/Modules/`. Now iterates `Application::getInstance()->getModulePathsForRoutes()`, so package-registered paths are auto-discovered.
- **`Start::getProtectedRoutes()`** merges `PROTECTED_ROUTES` from `.env` with routes registered by providers via `Application::addProtectedRoutes()`.
- **`Config.php` and `Environment.php` path resolution** updated to use `Application::getInstance()` for locating `.env`, defining `THIS_DIR`, `BASE_URL`, and `SITEPATH`.

### Fixed
- **All `SESSION_*` `.env` keys now actually control the session** — `Config.php::initConfig()` previously built `session_set_cookie_params()` from the hardcoded local `$config` array, silently ignoring `SESSION_COOKIE`, `SESSION_DOMAIN`, `SESSION_SAME_SITE`, and `SESSION_HTTP_ONLY`. Now reads from `ConfigManager::get('session.cookie.*')` with fallbacks.
- **`MailController` reads mail settings reliably** — was reading `$_ENV['MAIL_HOST']` etc. `putenv()` does not populate `$_ENV`, so those keys came back empty. Changed to `Environment::get('MAIL_HOST')`.
- **`ROUTE_USE_CACHE` was a dead knob** — `InitModsImproved` ignored the `.env` flag and always derived caching from `Environment::isProduction()`. Now checks `ROUTE_USE_CACHE` first; falls back to production auto-detect only when the key is absent.
- **`DB_CHARSET` now consistent across all paths** — `ConfigManager::loadDatabaseConfig()` hardcoded `utf8mb4`; now reads `Environment::get('DB_CHARSET', 'utf8mb4')`. `Database.php` already read the env key directly and remains unchanged.
- **`CacheManager` default driver reads `CACHE_DRIVER`** — `$defaultStore` was hardcoded `'file'`; `store()` now calls `ConfigManager::get('cache.default', 'file')` so `CACHE_DRIVER=array` in `.env` actually switches the driver.
- **`safe_glob` in `InitModsImproved`** — glob calls wrapped to return empty array on failure instead of `false`.

### Config
`.env` and `.env.example` fully audited and expanded. New keys documented with explanatory comments:
```
DB_CHARSET          SESSION_COOKIE      SESSION_DOMAIN      SESSION_SAME_SITE
SESSION_HTTP_ONLY   CORS_*              JWT_SECRET          JWT_ACCESS_TTL
JWT_REFRESH_TTL     RATE_LIMIT_*_MAX    RATE_LIMIT_*_WINDOW LOG_CHANNEL
LOG_LEVEL           LOG_PATH            ROUTE_DEBUG_OUTPUT  ROUTE_USE_CACHE
ROUTE_SUBMODULE_DISCOVERY               PROTECTED_ROUTES
```
`Environment::createDefaultEnvFile()` template updated to match — auto-created `.env` on first boot now includes all keys with production-safe defaults.

---

## v2.2.0 - Router Method Enforcement & CORS (2026-06-18)

### Added
- **Per-route HTTP method declaration** — `addRoute()` and `addParamRoute()` accept an optional `$methods` array. Methods are normalized to uppercase. Routes without a method declaration remain permissive (existing behaviour preserved).
- **405 Method Not Allowed** — when a route matches by path but the request method is not in its allowed list, the router returns HTTP 405 with an `Allow: GET, POST, ...` header and a JSON body `{"error": "Method Not Allowed", "allowed": [...]}`.
- **Route-level `cors` middleware** — routes can declare `['cors']` to opt into CORS headers per-route. Complements the existing global CORS middleware.
- **`Start::getCorsConfig()`** — private helper that normalises CORS config keys from `ConfigManager::get('app.cors')` and provides safe defaults. Used by both global CORS middleware and the `cors` named middleware to ensure a single source of truth.

### Changed
- **Unknown middleware throws** instead of silently passing — `Router::runMiddleware()` now throws `\RuntimeException` when a named middleware is not registered, making misconfiguration visible immediately.

---

## v2.1.1 - Cleanup & Composer Migration (2026-06-08)

### Added
- **PHPMailer via Composer** — removed the 8 000-line bundled PHPMailer source tree from `src/Modules/Mail/PHPMailer/`. Added `phpmailer/phpmailer` to `composer.json`. `MailController` and `MailRoutes` updated to use the installed package namespace.
- **`bv-nav.php` and `bv-styles.css`** — lightweight navigation and base stylesheet assets added to `src/Common/Assets/` for the built-in demo modules.

### Changed
- **`BaseView` modernised** — renders a full page shell (head, body, nav, footer) with inline styles. Removes the old per-module layout duplication in demo modules.
- **Auth hardened** — `Auth\Model` no longer accepts plain-text password queries; uses `password_hash` on create and `password_verify` on login. Removed timing-attack risk from the previous plain comparison.
- **`public/.htaccess` simplified** — removed 30 legacy/test-specific rewrite rules, leaving only the generic front-controller rule. All routing handled by `Router.php`.

### Removed
- **`src/Etc/InitMods.php`** (legacy module initialiser, superseded by `InitModsImproved`).
- **Admin module duplicates** — `Controllera`, `Controllerc`, `Controllerd` and their matching route files (`Routesa`, `Routesc`, `Routesd`) removed. Consolidated on the parameterised routing approach.
- **PHPUnit bumped to `^11.0`** in dev dependencies.

### Fixed
- PHPStan configuration updated — excludes adjusted for module view/template directories, generated test modules, and `src/Tools/`; `ignoreErrors` cleaned of stale InitMods rules.
- Several module minor fixes: `Newmod` `$orderBy` type corrected to `int`; `Reactnb` unused code removed; `User\Controller` `$output` initialised before use; `TestDashboard\Model::getRecentItems` signature corrected; `Testitems\Controller` redundant parent constructor call removed.

---

## v2.1.0 - JWT Foundation (2026-06-07)

### 🔐 JWT Support — Opt-In, No Breaking Changes

Completed the JWT infrastructure in core. Prior to this release, `JwtAuthMiddleware` existed for verifying tokens but there was no way to issue them — the framework had the verification side without the issuance side.

**New:**
- `src/Etc/JwtService.php` — HS256 token factory (`issueAccessToken`, `issueRefreshToken`, `getAccessTtl`, `getRefreshTtl`). Verification intentionally excluded — that remains `JwtAuthMiddleware`'s responsibility.
- `src/Etc/Start.php` — `JwtAuthMiddleware` now registered as named `'jwt'` middleware. Developers can protect any route with `['jwt']` without manual wiring.
- `docs/JWT_AUTHENTICATION.md` — complete guide covering `.env` config, issuing tokens, protecting routes, reading `$GLOBALS['current_user']`, refresh token schema, and session-vs-JWT decision table.

**Design:**
- Session-based auth is unchanged and remains the default for web apps.
- JWT is a second option for API routes, mobile backends, and SPAs — entirely opt-in.
- `JwtService` is a factory only. It does not duplicate `JwtAuthMiddleware`. The split mirrors the design validated in the crs-upmvc derived project.

**.env keys:**
```
JWT_SECRET=your-secret
JWT_ACCESS_TTL=3600
JWT_REFRESH_TTL=2592000
```

### ✅ Compatibility
No breaking changes. Existing routes, sessions, and middleware are untouched.

---

## v2.0.0 - Islands Release (2025-12-28)

### 🌍 Islands Architecture & NoFramework Identity

- Formalized the **Islands Architecture** direction for upMVC, with clear documentation of how PHP modules, React/Vue "islands", and micro‑frontends coexist.
- Clarified the **NoFramework** philosophy across docs (pure PHP + simple OOP, no heavy container/ORM requirements).
- Updated high‑level docs to speak to the target audience: developers who want structure without framework bloat.

**Key docs:**
- ISLANDS_ARCHITECTURE_INDEX.md – Islands overview and mental model
- ISLANDS_ARCHITECTURE.md – Deep dive into architecture
- ISLANDS_DOCUMENTATION_SUMMARY.md – Concise summary and navigation
- PHILOSOPHY_PURE_PHP.md – NoFramework positioning and principles

### 🔐 Auth, Middleware & Core Stability (v2.0 line)

- Fixed multiple critical authentication issues:
  - Intended URL handling in AuthMiddleware (no overwrite, reliable redirect).
  - Assignment vs comparison bug in auth checks.
  - Missing `exit` calls after redirects.
  - Trailing slash issues in redirect helpers.
- Removed all leftover debug code from production paths (router, auth module, middleware).
- Verified core request flow, URL handling and error behaviour via dedicated verification docs.

**Key docs:**
- READY_FOR_MAIN.md – v2.0 production‑readiness report
- PRE_RELEASE_VERIFICATION.md – Pre‑release verification checklist
- VERIFICATION_CHECKLIST.md – Code verification checklist (v2.0 layout)
- BUG_FIX_*.md, CLEANUP_DEBUG_CODE.md, URL_HANDLING_EXPLAINED.md – Detailed fix reports

### 🚦 Router v2.0 & Parameterized Routing (Version 2.0.0)

- Shipped **Router v2.0** with four major enhancements while keeping existing routes working:
  - **Type casting** – `{id:int}`, `{price:float}`, `{active:bool}` auto‑cast to correct types.
  - **Validation patterns** – Regex constraints at router level for safer parameter handling.
  - **Named routes** – `->name()` and helper support for refactor‑safe URL generation.
  - **Route grouping** – Prefix‑based optimization for large route sets.
- Added a full educational package: changelog, examples, implementation report, evaluation and recommendations.

**Key docs:**
- routing/ROUTER_V2_CHANGELOG.md – Router v2.0 changelog & migration guide
- routing/ROUTER_V2_EXAMPLES.md – Practical usage and migration examples
- routing/ROUTER_V2_IMPLEMENTATION_COMPLETE.md – Implementation report & merge instructions
- routing/PARAMETERIZED_ROUTING.md – In‑depth parameterized routing guide
- ROUTER_V2_STATUS.md – Overall status and recommended tagging (`v2.0.0`)

### 🧰 Helper System & Developer Experience (v1.4.7 → folded into v2 line)

- Introduced a modern **PSR‑4 modular helper system** and integrated it into the v2 codebase:
  - Replaced the monolithic `helpers.php` with dedicated helper classes (RouteHelper, UrlHelper, FormHelper, DataHelper, ResponseHelper, DebugHelper).
  - Added `HelperFacade` as a clean entry point, wired from Start/Router.
  - All helpers are now PSR‑4 autoloaded and easier to test and extend.
- Improved documentation around configuration, environment handling, and core areas.

**Key docs:**
- CORE_AREAS_AND_CONFIGURATION.md – Core areas and config map
- CONFIGURATION_FALLBACKS.md, CONFIG_SIMPLE_EXPLANATION.md – Env/config behaviour
- COMPONENT_LIBRARY.md – Modern component patterns and UI pieces

### 📊 CRUD, Dashboard & Admin Enhancements

- Implemented a modern CRUD + Dashboard flow with pagination, flash messages and demo‑data fallbacks.
- Added a robust dashboard module with stats widgets and recent‑items listings.

**Key docs:**
- docs/CHANGELOG-CRUD-PAGINATION.md – Full CRUD & dashboard report

### ✅ Quality, Verification & Tooling

- Established a **pre‑release verification pipeline** for the v2 line.
- Added maintenance tooling and docs for cache cleanup and module discovery.

---

## v1.4.7 - PSR-4 Helper Architecture (2025-11-09)

### 🏗️ Architecture: PSR-4 Modular Helper System

Refactored the monolithic helper system into a modern PSR-4 compliant modular architecture.

- Replaced single `etc/helpers.php` with organized `etc/Helpers/` namespace
- Implemented Facade pattern for clean helper access
- Each helper responsibility now in its own class
- PSR-4 autoloading replaces manual `require_once`

**New structure:** `etc/Helpers/` — HelperFacade, RouteHelper, UrlHelper, FormHelper, DataHelper, ResponseHelper, DebugHelper

### ✅ Compatibility
100% backward compatible. Same API via HelperFacade.

---

## v1.4.6 - Utilities & Robustness (2025-11-08)

- Module route discovery now tolerant to both `Routes($router)` and `routes($router)` method names.
- Added `tools/cache-cli.php` with `list`, `stats`, `clear:modules`, `clear:admin`, `clear:all` commands.

---

## v1.4.5 - Library Installation Fix (2025-10-23)

- **Router.php**: Fixed 404 page path for `composer require` (library install) compatibility.

---

## v1.4.4 - Lightweight Parameterized Routing (2025-11-08)

- Added `Router::addParamRoute()` with placeholder syntax (`{id}`, `{id:int}`), type casting, regex constraints, and named routes via `->name()`.
- Backward compatible — exact routes checked first, param routes as fallback.

**Key doc:** `docs/routing/PARAMETERIZED_ROUTING.md`

---

## v1.4.1 - Stable Dependencies (2025-10-23)

- Switched `minimum-stability` to `stable`, pinned `gabordemooij/redbean` to `^5.7`.

---

## v1.4.0 - Thrive (2025-10-23)

Major maturity release: React HMR, Islands Architecture, Security module, enhanced Router/Cache/Config, PSR-4 helpers foundation, 40+ new docs, 91 files changed.

---

## v1.0.3
Core framework features and initial module system.

## v1.0.2
Early framework development.

## v1.0.1
Initial release.
