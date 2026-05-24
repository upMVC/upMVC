# Bug: JWT_SECRET always empty — `$_ENV` not populated by `putenv()`

**Affects:** `src/Etc/Middleware/JwtAuthMiddleware.php`, `src/Etc/JwtService.php`  
**Severity:** Critical — JWT authentication always fails in standard Apache/mod_php deployments  
**Status:** Fixed (see below)

---

## Root Cause

PHP's `$_ENV` superglobal is populated **once at request startup** from the OS-level environment (controlled by `variables_order` in `php.ini`). It is **not** updated by runtime calls to `putenv()`.

`Environment::loadEnvFile()` parses `.env` and calls:
```php
self::$vars[$key] = $value;
putenv("{$key}={$value}");   // ← does NOT touch $_ENV
```

`JwtAuthMiddleware` and `JwtService` were reading:
```php
$secret = $_ENV['JWT_SECRET'] ?? '';   // always '' — JWT_SECRET is never in the OS env
```

Result: every JWT verification returns `null` → every protected API route returns `401 Invalid or expired token`.

---

## Secondary Issue: Windows Apache Thread-Safety

On Windows, Apache uses a **threaded MPM** (winnt). PHP is compiled in ZTS (Zend Thread Safety) mode: each request runs in its own thread with its own PHP globals, but the underlying C-runtime environment (`putenv`/`getenv`) is process-wide with no thread synchronisation.

When two requests arrive simultaneously (e.g. browser fires dashboard + badge-refresh concurrently):

1. Thread A calls `putenv('JWT_SECRET=...')` → sets the CRT env for the process
2. Thread B calls `getenv('JWT_SECRET')` → may catch the CRT env **before** Thread A's write is visible (race window is tiny but real)
3. Thread B gets empty secret → `401`

This was the live symptom in the `crs-upmvc` project: platform-admin login succeeded but immediately logged out because the concurrent badge-refresh request returned `401`.

---

## Why `Environment::$vars` is Safe

`Environment::$vars` is a PHP `static array`. In PHP-ZTS, each thread has its own execution environment including class statics — they are **not shared** between threads. Each request thread runs `Environment::load()` independently from the `.env` file, populating its own `$vars` copy. No cross-thread race is possible.

---

## The Fix

Read from `Environment::get()` as the primary source, with `getenv()` and `$_ENV` as fallbacks for edge cases (CLI, PHP-FPM, or environments where the secret is set at the OS level):

```php
use App\Etc\Config\Environment;

// In JwtAuthMiddleware::verifyJwt():
$secret = (string) (Environment::get('JWT_SECRET') ?: getenv('JWT_SECRET') ?: ($_ENV['JWT_SECRET'] ?? ''));

// In JwtService::__construct():
$this->secret     = (string) (Environment::get('JWT_SECRET')     ?: getenv('JWT_SECRET')     ?: ($_ENV['JWT_SECRET']      ?? ''));
$this->accessTtl  = (int)   (Environment::get('JWT_ACCESS_TTL')  ?: getenv('JWT_ACCESS_TTL')  ?: ($_ENV['JWT_ACCESS_TTL']  ?? 3600));
$this->refreshTtl = (int)   (Environment::get('JWT_REFRESH_TTL') ?: getenv('JWT_REFRESH_TTL') ?: ($_ENV['JWT_REFRESH_TTL'] ?? 2592000));
```

---

## Affected Projects

| Project | Original bug | Fixed |
|---|---|---|
| `upMVC` | `$_ENV['JWT_SECRET']` — always empty | Yes — `Environment::get()` added |
| `crs-upmvc` | `getenv('JWT_SECRET')` — thread-unsafe | Yes — `Environment::get()` added |

---

## General Rule

Anywhere in upMVC that reads a `.env` value — **always use `Environment::get('KEY')`**, never `$_ENV['KEY']` or bare `getenv('KEY')`. Only the `Environment` class guarantees the value was actually loaded from the `.env` file.
