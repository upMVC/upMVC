# Changelog

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
