# Changelog

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
  - Added `HelperFacade` (or equivalent) as a clean entry point, wired from Start/Router.
  - All helpers are now PSR‑4 autoloaded and easier to test and extend.
- Improved documentation around configuration, environment handling, and core areas.

**Key docs:**
- CORE_AREAS_AND_CONFIGURATION.md – Core areas and config map
- CONFIGURATION_FALLBACKS.md, CONFIG_SIMPLE_EXPLANATION.md – Env/config behaviour
- COMPONENT_LIBRARY.md – Modern component patterns and UI pieces

### 📊 CRUD, Dashboard & Admin Enhancements

- Implemented a modern CRUD + Dashboard flow with pagination, flash messages and demo‑data fallbacks.
- Added a robust dashboard module with stats widgets and recent‑items listings.
- Documented the CRUD/dashboard implementation and moved the detailed report under docs.

**Key docs:**
- docs/CHANGELOG-CRUD-PAGINATION.md – Full CRUD & dashboard report
- CHANGELOG-CRUD-PAGINATION.md (root) – Pointer to the detailed docs file

### ✅ Quality, Verification & Tooling

- Established a **pre‑release verification pipeline** for the v2 line, including:
  - Manual checklists for core files, middleware, auth, and routes.
  - Guidance for running PHPUnit tests (including RouterEnhanced tests).
  - Git workflows for merging feature branches (auth fixes, router v2) into `main`.
- Added maintenance tooling and docs for cache cleanup and module discovery.

**Key docs & tools:**
- PRE_RELEASE_VERIFICATION.md, READY_FOR_MAIN.md, VERIFICATION_CHECKLIST.md
- ROUTER_V2_STATUS.md and routing/ROUTER_V2_* docs
- tools/cache-cli.php – Cache maintenance CLI (modules/admin caches)

---

## v1.4.7 - PSR-4 Helper Architecture (2025-11-09)

### 🏗️ Architecture: PSR-4 Modular Helper System

Refactored the monolithic helper system into a modern PSR-4 compliant modular architecture.

**What changed:**
- Replaced single `etc/helpers.php` file with organized `etc/Helpers/` namespace
- Implemented Facade pattern for clean helper access
- Each helper responsibility now in its own class
- PSR-4 autoloading replaces manual `require_once`
- Follows PHP-FIG standards and best practices

**New Structure:**
```
etc/Helpers/
├── HelperFacade.php      # Main entry point (facade pattern)
├── RouteHelper.php       # Named routes & redirection
├── UrlHelper.php         # URL & asset generation
├── FormHelper.php        # Form helpers & CSRF
├── DataHelper.php        # Session, config, env access
├── ResponseHelper.php    # Views, JSON, HTTP responses
└── DebugHelper.php       # dd(), dump() utilities
```

**Benefits:**
- ✅ PSR-4 compliant - autoloaded, no manual require
- ✅ Single Responsibility - each helper has one purpose
- ✅ Scalable - easy to add new helper classes
- ✅ Maintainable - organized by functionality
- ✅ Testable - individual helper classes can be unit tested
- ✅ Clean dependencies - facade delegates to specialists

**Usage:**
```php
// In etc/Start.php (PSR-4 autoloaded)
use upMVC\Helpers\HelperFacade;
HelperFacade::setRouter($router);

// In controllers/views
HelperFacade::route('user.edit', ['id' => 5]);
HelperFacade::url('/path/to/resource');
HelperFacade::redirect('/dashboard');
HelperFacade::view('admin/users', ['users' => $users]);
HelperFacade::dd($variable);
```

**Helper Classes:**

1. **HelperFacade** - Main entry point
   - Delegates to specialized helpers
   - Provides unified API
   - Manages Router dependency injection

2. **RouteHelper** - Routing functionality
   - `route($name, $params)` - Generate named route URLs
   - `redirect($to, $params, $status)` - HTTP redirects

3. **UrlHelper** - URL generation
   - `url($path)` - Generate full URLs with BASE_URL
   - `asset($path)` - Generate asset URLs

4. **FormHelper** - Form utilities
   - `old($key, $default)` - Repopulate form fields
   - `csrfToken()` - Get CSRF token
   - `csrfField()` - Generate CSRF hidden field

5. **DataHelper** - Data access
   - `session($key, $default)` - Access session data
   - `config($key, $default)` - Access configuration
   - `env($key, $default)` - Access environment variables
   - `request($key, $default)` - Access request data

6. **ResponseHelper** - HTTP responses
   - `view($path, $data)` - Render views
   - `abort($code, $message)` - HTTP error responses
   - `json($data, $status)` - JSON responses

7. **DebugHelper** - Development utilities
   - `dd(...$vars)` - Dump and die
   - `dump(...$vars)` - Dump variables

### 🔧 Technical Details

**Composer Autoload:**
```json
"autoload": {
    "psr-4": {
        "upMVC\\Helpers\\": "etc/Helpers/",
        "upMVC\\": "common/",
        // ... other namespaces
    }
}
```

**Dependency Injection:**
```php
// Router instance injected into facade
HelperFacade::setRouter($router);

// Facade delegates to RouteHelper with router
RouteHelper::setRouter($router);
```

**Design Patterns:**
- Facade Pattern: Single entry point delegates to specialists
- Single Responsibility: Each class handles one concern
- Static Factory: Convenient static method access
- Dependency Injection: Router injected, not hard-coded

### 📦 Files Changed

**New:**
- `etc/Helpers/HelperFacade.php` - Main facade class
- `etc/Helpers/RouteHelper.php` - Routing helper
- `etc/Helpers/UrlHelper.php` - URL helper
- `etc/Helpers/FormHelper.php` - Form helper
- `etc/Helpers/DataHelper.php` - Data access helper
- `etc/Helpers/ResponseHelper.php` - Response helper
- `etc/Helpers/DebugHelper.php` - Debug helper

**Modified:**
- `composer.json` - Added `upMVC\\Helpers\\` PSR-4 namespace
- `src/Etc/Start.php` - Uses `HelperFacade` via PSR-4 (no require_once)

**Deleted:**
- `etc/helpers.php` - Replaced by modular structure (moved under `src/Etc/Helpers/`)

### ✅ Compatibility

- **100% backward compatible** - Same API via HelperFacade
- **No controller changes required** - Same method signatures
- **Autoloaded automatically** - Composer handles class loading
- **Easier to extend** - Add new helper classes as needed

### 🎓 Educational Value

This refactoring demonstrates:
1. **PSR-4 Standards** - Following PHP-FIG autoloading standard
2. **Facade Pattern** - Clean delegation to specialized classes
3. **Single Responsibility** - Each class has one job
4. **Modular Architecture** - Organized by functionality
5. **Dependency Injection** - Router injected, not coupled

### 🚀 Future Enhancements

Possible additions:
- Global function wrappers (procedural API: `route()`, `url()`, etc.)
- Additional helpers (CacheHelper, ValidationHelper, etc.)
- Helper service providers for DI container
- Unit tests for each helper class

---

## v1.4.6 - Utilities & Robustness (2025-11-08)

### 🔧 Improvements
- Module route discovery is now tolerant to both `Routes($router)` and `routes($router)` method names.
  - `etc/InitModsImproved.php` auto-detects and invokes either variant, validates signature, and logs helpful errors.
  - Improves developer ergonomics without requiring module code changes.

### 🧹 New: Cache Maintenance CLI
- Added `tools/cache-cli.php` with the following commands:
  - `list` / `help` – show available commands
  - `stats` – display module discovery and admin route cache statistics
  - `clear:modules` – clear module discovery caches
  - `clear:admin` – clear Admin module dynamic route cache
  - `clear:all` – clear modules + admin caches and flush all cache stores
- Documented in README under "Maintenance: Cache CLI" with Windows PowerShell examples.

### 📚 Docs
- Updated `README.md` with a new section describing the Cache CLI usage and affected components.

### ✅ Compatibility
- No breaking changes. Existing modules and routes continue to work as before.

## v1.4.5 - Thrive (Library Installation Fix) (2025-10-23)

### 🐛 Bug Fixes
- **Router.php**: Fixed 404 page path for library installation compatibility
  - Changed from `./common/404.php` to `__DIR__ . '/../common/404.php'`
  - Now works correctly when installed via `composer require` in vendor directory
  - Maintains compatibility with standalone installation

### 📦 Installation
This version is essential for users installing upMVC as a library:
```bash
composer require bitshost/upmvc:^1.4.2
```

## v1.4.4 - Lightweight Parameterized Routing (2025-11-08)

### ✨ Feature: Parameterized Routing

Added optional parameterized routing support via `Router::addParamRoute()`.

**What it is:**
- Define routes with placeholders: `/users/{id}`, `/orders/{orderId}/items/{itemId}`
- Router extracts params and injects into `$_GET` (e.g., `$_GET['id'] = '123'`)
- Also available in middleware via `$request['params']`
- Backward compatible - works alongside exact routes

**How it works:**
1. Exact routes checked first (O(1) hash lookup)
2. If no exact match, parameterized routes evaluated (segment matching)
3. Captured values injected into `$_GET` for controller access
4. Controller validates params (type, existence, permissions)

**When to use:**
- ✅ Large datasets (1,000+ records) - collapses thousands of routes to a few patterns
- ✅ Dynamic content (blogs, shops) - no cache invalidation needed
- ✅ RESTful APIs - clean resource URLs
- ✅ Memory constraints - O(1) storage vs O(N) expansion

**When NOT to use:**
- ❌ Small datasets (< 1,000 records) - cached expansion is simpler
- ❌ Security-first apps - cached expansion pre-validates IDs
- ❌ Stable data - cache strategy works great for infrequent changes

### 📘 Documentation

Comprehensive documentation created:
- **[docs/routing/PARAMETERIZED_ROUTING.md](docs/routing/PARAMETERIZED_ROUTING.md)** - Complete guide (12,000+ words)
  - When to use (decision tree)
  - Admin module example (both strategies shown)
  - Strategy comparison table
  - Implementation details
  - Controller integration patterns
  - Advanced patterns (nested resources, multiple params)
  - Performance benchmarks
  - Migration guide
  - Best practices

- **Updated:** [docs/routing/README.md](docs/routing/README.md) - Quick start with param routing
- **Updated:** [modules/admin/README.md](modules/admin/README.md) - Documents both strategies
- **Updated:** Main [README.md](README.md) - Feature overview

### 🧪 Examples

**Admin Module - Dual Implementation (Educational):**

The admin module now serves as a **teaching example** showing both approaches:

1. **Current (Routes.php, Controller.php):** Parameterized routing
   - `$router->addParamRoute('/admin/users/edit/{id}', ...)`
   - Controller reads `$_GET['id']` and validates
   - Scalable to millions of users
   - No cache needed

2. **Backup (Routesc.php, Controllerc.php):** Cached expansion
   - Pre-generates explicit route for each user
   - Cached to `etc/storage/cache/admin_routes.php`
   - Security-first (only valid IDs routable)
   - Perfect for small projects

**Test Module:**
- Added `/test/item/{id}` and `/test/pair/{first}/{second}` examples
- Demonstrates basic param extraction and validation

### 🔧 Technical Details

**Router enhancements:**
```php
// New property
protected $paramRoutes = [];

// New method
public function addParamRoute(
    string $pattern,
    string $className,
    string $methodName,
    array $middleware = []
): void

// New private method
private function matchParamRoute(string $reqRoute): ?array
```

**Dispatcher flow:**
```
Request → Exact match? → Yes → Execute
                      → No  → Param match? → Yes → Inject $_GET → Execute
                                           → No  → 404
```

**Parameter injection:**
```php
// Pattern: /products/{id}
// Request: /products/123
// Result: $_GET['id'] = '123' + $request['params']['id'] = '123'
```

### ✅ Compatibility

- **100% backward compatible** - existing exact routes work unchanged
- **No .htaccess changes** - works with existing rewrite rules
- **No controller changes required** - controllers already use $_GET
- **Optional adoption** - use only where beneficial
- **Method guard available** - `method_exists($router, 'addParamRoute')`

### 🚀 Performance

**Benchmarks (10,000 users):**

| Strategy | Registration | Memory | First Request |
|----------|-------------|---------|---------------|
| Cached expansion | 2ms | 2MB | 2ms |
| Parameterized | 0.5ms | 20KB | 0.5ms |

**Scalability:**
- 100 users: Both work well
- 1,000 users: Param routes start winning
- 10,000 users: Param routes 4x faster, 100x less memory
- 100,000+ users: Only param routes viable

### 🔒 Security Notes

**Cached expansion (Routesc.php):**
- ✅ Pre-validates IDs at router level
- ✅ Invalid IDs get 404 before reaching controller
- ✅ Requires cache regeneration on data changes

**Parameterized (Routes.php):**
- ⚠️ All IDs reach controller (must validate)
- ✅ Always shows current data (no cache invalidation)
- ✅ Example validation patterns provided in docs

### 🎓 Educational Value

Admin module now teaches:
1. **When to use each strategy** - dataset size decision tree
2. **How caching works** - see Routesc.php implementation
3. **How param routing works** - see Routes.php implementation  
4. **Migration path** - docs show step-by-step conversion
5. **Trade-offs** - security vs scalability vs complexity

---

## v1.4.1 - Thrive (Stable Dependencies) (2025-10-23)

### 🔧 Improvements
- **Composer Dependencies**: Changed to stable versions for easier installation
  - `minimum-stability`: Changed from "dev" to "stable"
  - `gabordemooij/redbean`: Changed from "dev-master" to "^5.7"
  - Added `prefer-stable: true` for better dependency resolution

### 📦 Installation
Now installs cleanly from Packagist without stability warnings:
```bash
composer require bitshost/upmvc:^1.4.1
```

---

## v1.4.0 - Thrive (2025-10-23)

### 🌱 Major Framework Maturity Update

This release represents a significant milestone in upMVC's evolution, with comprehensive enhancements across all core components, extensive documentation, and revolutionary new features.

### 🚀 New Features

#### React Integration & Islands Architecture
- **React HMR Module**: Complete Hot Module Replacement support for React development
  - Live reload without losing component state
  - Fast refresh integration
  - Development server with HMR support
  - Component library included
- **Islands Architecture**: Modern partial hydration approach
  - Interactive islands embedded in static pages
  - Reduced JavaScript bundle size
  - Improved performance and SEO
  - Complete documentation with examples (Chart, Form, Search islands)
- **React Integration Patterns**: Comprehensive React integration strategies
  - State management solutions
  - Routing strategies for React + PHP
  - Component organization patterns

#### Enhanced Core System
- **Security Module**: New centralized security handling (`etc/Security.php`)
- **Enhanced Error Handler**: Improved error handling with detailed logging (`etc/ErrorHandler.php`)
- **Router Enhancements**: Advanced routing capabilities
  - Pattern matching improvements
  - Cache integration
  - Better route organization
- **Cache System Overhaul**: 
  - Enhanced `CacheManager` with advanced features
  - File-based caching with automatic cleanup
  - TTL support and cache invalidation
  - Admin cache management interface
- **Config System Improvements**:
  - Better environment variable handling
  - Simplified configuration access
  - Config customization guide
  - Protected routes implementation

#### Module System
- **Admin Module Enhanced**: 
  - Cache management interface
  - Improved controller with authentication
  - Better view templates
  - Route protection
- **React HMR Module**: Complete React development environment
- **ReactNB Module**: React notebook/notes module

### 📚 Documentation Explosion (40+ New Docs!)

#### Core Documentation
- **PROGRESS.md**: Framework development progress tracking
- **Component Library**: Complete UI component documentation
- **Module Philosophy**: Architectural decisions explained
- **Config Guides**: Simple explanations and customization guides
- **Protected Routes**: Implementation and solutions documented

#### React Documentation Suite
- **REACTHMR_COMPLETE.md**: Complete HMR implementation guide
- **REACTHMR_IMPLEMENTATION.md**: Step-by-step implementation
- **REACTHMR_QUICK_SUMMARY.md**: Quick reference
- **REACTHMR_VISUAL_GUIDE.md**: Visual implementation guide
- **REACT_DOCUMENTATION_COMPLETE.md**: Comprehensive React docs
- **REACT_INTEGRATION_PATTERNS.md**: Integration strategies (1137 lines!)
- **REACT_PATTERNS_SUMMARY.md**: Pattern summaries
- **REACT_QUICK_REFERENCE.md**: Quick reference guide
- **STATE_MANAGEMENT.md**: State management strategies

#### Islands Architecture Documentation
- **ISLANDS_ARCHITECTURE.md**: Complete architecture guide (799 lines)
- **ISLANDS_ARCHITECTURE_INDEX.md**: Quick navigation
- **ISLANDS_DOCUMENTATION_SUMMARY.md**: Summary and overview
- **Examples**: Chart Island, Form Island, Search Island implementations

#### Routing Documentation
- **ROUTING_STRATEGIES.md**: Comprehensive routing guide (988 lines)
- **routing/README.md**: Routing system overview
- **routing/QUICK_REFERENCE.md**: Quick routing reference
- **routing/ORGANIZATION_SUMMARY.md**: Route organization strategies
- **Examples**: Pattern matching, caching, controller examples

#### Configuration Documentation
- **CONFIG_SIMPLE_EXPLANATION.md**: Easy-to-understand config guide
- **CONFIG_CUSTOMIZATION_GUIDE.md**: Customization strategies
- **CONFIG_QUICK_EDIT.md**: Quick config editing
- **UNDERSTANDING_CONFIG_ARRAY.md**: Deep dive into config
- **DOTENV_USAGE_EXAMPLES.md**: .env usage patterns
- **WHY_CONSTANTS_VS_ENV.md**: Design decisions explained
- **APP_URL_VS_DOMAIN_NAME_ANALYSIS.md**: URL handling analysis
- **GET_SET_USAGE_ANALYSIS.md**: Getter/setter pattern analysis
- **CLEANUP_DUPLICATE_CONFIGS.md**: Config cleanup guide
- **THE_FALLBACK_DISCOVERY.md**: Fallback mechanism explained

### 🔧 Improvements

#### Core Files Enhanced
- **Start.php**: Enhanced bootstrapping with better error handling
- **Router.php**: Improved routing logic and pattern matching
- **Config.php**: Simplified configuration management
- **Cache.php**: Enhanced caching with manager integration
- **Database.php**: Improved database connection handling
- **InitModsImproved.php**: Better module initialization

#### Middleware System
- **AuthMiddleware**: Enhanced authentication checks
- **CorsMiddleware**: Improved CORS handling
- **LoggingMiddleware**: Better request/response logging
- **MiddlewareManager**: Enhanced middleware pipeline

#### Configuration
- **.htaccess**: Improved rewrite rules
- **.env**: Updated environment configuration structure
- **composer.json**: Updated dependencies and autoloading

### 🐛 Bug Fixes
- Removed duplicate files (`Routero.php`, `Starto.php`)
- Fixed configuration loading issues
- Improved error handling across all modules
- Better cache invalidation

### 🔒 Security
- Deleted .env from repository (security best practice)
- Added .env.example for reference
- Enhanced authentication middleware
- Protected route implementation
- Security module for centralized security handling

### 📊 Statistics
- **91 files changed**
- **23,263 insertions** (new code/docs)
- **1,161 deletions** (cleanup)
- **40+ new documentation files**
- **3 new modules**
- **15 commits** since v1.0.3

### 🎯 Breaking Changes
None - Fully backward compatible

### 🙏 Contributors
Built with ❤️ by [BitsHost](https://bitshost.biz/)

### 📖 Migration Guide
No migration needed - this is a feature addition release. All existing code continues to work.

### 🔮 What's Next
The framework is now mature and thriving! Future releases will focus on:
- More React components
- Additional module examples
- Performance optimizations
- Extended documentation with video tutorials

---

## v1.0.3 (Previous Release)

Core framework features and initial module system.

---

## v1.0.2

Early framework development.

---

## v1.0.1

Initial release.
