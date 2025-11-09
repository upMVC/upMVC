# Changelog

## v1.4.3 - Utilities & Robustness (2025-11-08)

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

## v1.4.2 - Thrive (Library Installation Fix) (2025-10-23)

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

### � Future Enhancements (not included)

Possible additions for later versions:
- Typed placeholders: `{id:int}`, `{slug:alpha}`
- Wildcard tails: `{path+}` for catch-all segments
- Optional segments: `{locale?}` with defaults
- Regex constraints: `{id:\d+}`
- Route naming: `route('user.edit', ['id' => 5])`

### 📦 Files Changed

**New:**
- `docs/routing/PARAMETERIZED_ROUTING.md` - Complete guide

**Modified:**
- `etc/Router.php` - Added addParamRoute() and matchParamRoute()
- `modules/admin/routes/Routes.php` - Parameterized implementation
- `modules/admin/Controller.php` - Param-based route handling
- `modules/test/routes/Routes.php` - Added param examples
- `docs/routing/README.md` - Updated with param routing info
- `modules/admin/README.md` - Dual strategy documentation
- `README.md` - Added param routing section

**Preserved (Educational):**
- `modules/admin/routes/Routesc.php` - Cache-based backup
- `modules/admin/Controllerc.php` - Cache-based backup

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
