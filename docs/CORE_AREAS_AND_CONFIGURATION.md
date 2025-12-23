# Core Areas & Configuration Guide

This guide explains the major areas of the upMVC `src/` tree, what each part does, and how it is configured. It is the missing link between the high-level philosophy docs and the concrete files in `src/`.

> Audience: developers who already have upMVC running and now want to understand "what lives where" and how to configure it.

---

## 1. Etc/ – Core Engine

Path: `src/Etc`

This is the heart of upMVC. It contains bootstrap, routing, configuration, security, and low-level helpers.

### 1.1 Startup Flow

**Key files:**
- `Start.php` – Application bootstrap
- `Routes.php` – Registers system routes and module routes
- `Router.php` – Routing engine
- `InitModsImproved.php` – Modern module discovery/registration

**How it works (HTTP request):**
1. Web server points to `public/index.php` (or root `index.php`).
2. Index bootstraps Composer and instantiates `App\Etc\Start`.
3. `Start::__construct()` calls:
   - `bootstrapApplication()` → `ConfigManager::load()`, error handling
   - `initializeRequest()` → captures URI/method and normalizes route via `Config`.
4. `Start::upMVC()` creates a `Router`, wires middleware, then calls `Routes::startRoutes()`.

Configuration links:
- Uses `App\Etc\Config\ConfigManager` and `App\Etc\Config\Environment` for app, DB, CORS, security.
- Uses `LOG_PATH` from `.env` (via `Config`) to configure core error logging.

### 1.2 Configuration Layer

**Namespace:** `App\Etc\Config`

**Main classes:**
- `Environment` – `.env` loader & creator (`src/Etc/.env`)
- `ConfigManager` – modern config registry (`app`, `database`, `cache`, `session`, `security`, `mail`, `logging`)
- `Config.php` (in `App\Etc`) – legacy-style config + bootstrapping helpers

**Environment (`Environment.php`):**
- Creates a default `.env` if none exists.
- Supports `${VAR}` and `$VAR` substitution.
- Makes values accessible via `Environment::get('KEY')` and helpers (`isDevelopment()` etc.).

**ConfigManager (`ConfigManager.php`):**
- Reads `Environment` and builds structured config arrays:
  - `app.*` – `APP_NAME`, `APP_ENV`, `APP_DEBUG`, `DOMAIN_NAME`, `SITE_PATH`, `APP_TIMEZONE`, `APP_LOCALE`, CORS.
  - `database.*` – `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`.
  - `cache.*` – `CACHE_DRIVER`, `CACHE_TTL`.
  - `session.*` – all `SESSION_*` variables.
  - `security.*` – `CSRF_PROTECTION`, `RATE_LIMIT`.
  - `mail.*` – all `MAIL_*` variables.
  - `logging.*` – `LOG_CHANNEL`, `LOG_LEVEL`.

**Config (`Config.php`):**
- Called from `Start::bootstrapApplication()` indirectly.
- Responsibilities:
  - Sets PHP timezone (prefers `app.timezone`).
  - Applies `APP_DEBUG` / `app.debug` to `error_reporting` and `display_errors`.
  - Defines `THIS_DIR`, `BASE_URL`, `SITEPATH`.
  - Starts the PHP session with reasonable defaults.
  - Configures `App\Etc\ErrorHandler` with `LOG_PATH` from `.env` (default `src/logs`).

### 1.3 Error Handling

**Classes:**
- `App\Etc\ErrorHandler` – static/global error logger
- `App\Etc\Exceptions\ErrorHandler` – OO handler used by `Start`

Behavior:
- Static handler writes daily JSON logs to `error_YYYY-MM-DD.log` under the directory set by `LOG_PATH`.
- OO handler writes to `logs/errors.log` and controls error pages for HTTP.

Key configuration:
- `.env` → `APP_DEBUG` decides if errors are visible or hidden.
- `.env` → `LOG_PATH` decides where the main JSON error logs go.

### 1.4 Security & Middleware

**Security (`Security.php`):**
- CSRF token generation and validation.
- Simple rate limiting (`Security::rateLimit`).
- Input sanitization helpers.

**Middleware (`Middleware/`):**
- `LoggingMiddleware` – basic request logging.
- `CorsMiddleware` – attaches CORS headers based on `app.cors.*` config.
- `AuthMiddleware` – guards protected routes.

Configuration:
- `.env` → `CORS_ENABLED`, `CORS_ALLOWED_ORIGINS`, `CORS_ALLOWED_METHODS`, `CORS_ALLOWED_HEADERS`.
- `.env` → `CSRF_PROTECTION`, `RATE_LIMIT`.
- `.env` → `PROTECTED_ROUTES` (comma-separated), otherwise defaults from `Start::$defaultProtectedRoutes`.

### 1.5 Helpers

**Namespace:** `App\Etc\Helpers`

**Entry point:** `HelperFacade`

Delegates to:
- `RouteHelper` – URL generation, redirects.
- `UrlHelper` – `url()`, `asset()`.
- `FormHelper` – CSRF fields, `old()` form values.
- `DataHelper` – access to `session`, `config`, `env`, `request`.
- `ResponseHelper` – `view()`, `json()`, `abort()`.
- `DebugHelper` – `dd()`, `dump()`.

Usage:
- `Start` calls `HelperFacade::setRouter($router)` so helpers can build URLs.
- In controllers/views you can use the helper functions (documented in HOW-TO-GUIDE) instead of touching Router directly.

### 1.6 Advanced Infrastructure (Optional Today)

**Cache (`Cache.php` and `Cache/`):**
- `CacheManager`, `FileCache`, `ArrayCache`, `CacheInterface`.
- Wired to `ConfigManager` (`cache.*`), but not used by demo modules yet.
- Ready to be used in your own modules: `CacheManager::remember('key', fn() => ...);`.

**Container (`Container/`):**
- Simple dependency injection container (`Container`, `ServiceProviderInterface`).
- Not yet used in the core bootstrap.
- Intended for advanced users who want DI but still keep NoFramework simplicity.

**Events (`Events/`):**
- `Event` base class and `EventDispatcher` for pub/sub patterns.
- Not wired into the current request flow; available as an optional building block.

---

## 2. Modules/ – Application Features

Path: `src/Modules`

Each folder under `Modules/` is a self-contained feature or domain (e.g. `Test`, `User`, `TestCrud`).

Typical structure:
- `Controller.php` – HTTP entry points
- `Model.php` – database access (usually extends `Common\Bmvc\BaseModel`)
- `View.php` – rendering logic
- `Routes/Routes.php` – route definitions for the module
- `etc/` – module-specific docs or configs (API docs, examples)

Registration:
- `InitModsImproved.php` discovers and registers modules.
- `Routes.php` imports module routes into the main router.

Configuration:
- Modules can read `Environment::get()` or `ConfigManager::get()` for DB, cache, mail, etc.
- Protected/unprotected status is controlled by global auth middleware + route patterns.

For more philosophy and patterns, see:
- `docs/MODULE_PHILOSOPHY.md`
- `docs/MODULE_PHILOSOPHY_UPDATE.md`

---

## 3. Common/ – Shared Base Classes

Path: `src/Common`

**Purpose:** shared building blocks reused by many modules.

Examples:
- `Common/Bmvc/BaseController.php` – base controller with render/json helpers.
- `Common/Bmvc/BaseModel.php` – base model that obtains a PDO connection from `Database`.

Configuration links:
- DB credentials come from `Environment` and `ConfigManager` (see Section 1.2).
- Error handling and logging are already configured globally via `Start`/`Config`.

---

## 4. Tools/ – Developer Utilities

Path: `src/Tools`

These are not part of the runtime HTTP request flow. They are **developer tools** for generating code and managing modules.

Key items:
- `ModuleGeneratorEnhanced/ModuleGeneratorEnhanced.php` – generates full modules (controllers, models, views, routes) that plug into `InitModsImproved`.
- `modulegenerator/`, `crudgenerator/`, `createmodule/` – earlier generators kept for backwards compatibility and experimentation.
- `cache-cli.php` – CLI helper for cache operations.

Usage:
- Run via PHP CLI from project root, e.g.:
  - `php src/Tools/ModuleGeneratorEnhanced/quick-gen-test.php`
- Generators create code under `src/Modules/...` following the PSR-4 structure.

Configuration:
- Generators read DB settings and environment via `App\Etc\Config\Environment`.
- They assume the same `.env` configuration as the main application.

---

## 5. Logs, Storage, and Paths

Relevant paths:
- `src/Etc/.env` – main environment file (auto-created if missing).
- `src/logs/` – default error log directory (overridden by `LOG_PATH`).
- `src/Etc/storage/` – storage area for sessions/cache (used by future features).

Important `.env` keys (see detailed .env docs and vault report):
- `APP_ENV`, `APP_DEBUG` – environment and debug mode.
- `DOMAIN_NAME`, `SITE_PATH` – URL building and routing.
- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` – database connection.
- `CSRF_PROTECTION`, `RATE_LIMIT` – security middleware behavior.
- `CORS_ENABLED`, `CORS_ALLOWED_*` – CORS middleware.
- `LOG_PATH` – where the JSON error logs are written.

---

## 6. How to Extend Safely

When adding new features:

- Place shared infrastructure in `src/Etc` **only if** it is truly framework-level (routing, config, security, helpers).
- Place reusable building blocks in `src/Common`.
- Place business features in `src/Modules/YourModule`.
- Use `Environment` and `ConfigManager` instead of hardcoding values.
- Prefer middleware (`Middleware/`) for cross-cutting concerns (auth, logging, throttling).
- Keep dev-only tools in `src/Tools` or a dedicated `tools/` directory.

This separation keeps the NoFramework core clean and makes it clear where to look when you want to change configuration versus business logic.
