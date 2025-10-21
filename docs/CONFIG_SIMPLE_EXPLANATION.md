# Quick Answer: What Does $config Do?

## 🎯 Simple Answer

`$config` is like a **settings box** that holds application settings like debug mode, timezone, session config, etc.

**NO, it doesn't overwrite anything!** It's completely separate from `$fallbacks` and `.env`.

---

## 📦 Three Separate Storage Boxes

```
┌──────────────────────┐
│    $fallbacks        │  Box #1: Just site_path & domain_name
│  - site_path         │         (backup if .env missing)
│  - domain_name       │
└──────────────────────┘

┌──────────────────────┐
│     $config          │  Box #2: General settings
│  - debug             │         (used by app internally)
│  - timezone          │
│  - session [...]     │
│  - cache [...]       │
│  - security [...]    │
└──────────────────────┘

┌──────────────────────┐
│    .env file         │  Box #3: Environment config
│  SITE_PATH=...       │         (HIGHEST PRIORITY)
│  DOMAIN_NAME=...     │
│  DB_HOST=...         │
│  etc...              │
└──────────────────────┘
```

**They NEVER conflict!** Each has its own purpose.

---

## 🔍 Where Each is Used

### `$fallbacks` Used By:
```php
Config::getSitePath()      → Checks .env, falls back to $fallbacks
Config::getDomainName()    → Checks .env, falls back to $fallbacks
```

### `$config` Used By:
```php
Config::get('debug')       → Gets from $config
Config::get('timezone')    → Gets from $config
Config::get('session.name') → Gets from $config
Config::set('debug', false) → Changes $config
```

### `.env` Used By:
```php
Environment::get('SITE_PATH')    → Gets from .env
Environment::get('DB_HOST')      → Gets from .env
ConfigManager::get('app.debug')  → Gets from .env
```

---

## 💡 Real Example

```php
// Startup sequence:

// 1. Application loads .env
Environment::load();  // Box #3 populated

// 2. initConfig() runs
date_default_timezone_set(self::get('timezone', 'UTC'));
//                        ↑ Gets 'UTC' from $config (Box #2)

// 3. User calls getSitePath()
Config::getSitePath();
// ↓ Checks .env (Box #3) first
// ↓ If not found, uses $fallbacks (Box #1)
```

---

## ✅ Bottom Line

**$config is NOT overwriting anything!**

It's just a storage array for:
- Debug mode
- Timezone
- Session settings
- Cache settings
- Security settings

**Used internally by `Config::get()` and `initConfig()`**

Think of it as the **"general settings"** box, while:
- `$fallbacks` = "path/domain backup" box
- `.env` = "environment values" box

**All three work together without conflicts!** 🚀
