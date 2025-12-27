# Install upMVC as a Library

This guide shows how to use upMVC as a *library* inside another PHP application.

## 1. Create a host folder (example: `archive/lib`)

In your host app, create a folder that will host this upMVC instance, e.g.:

- `archive/lib/`

Inside it, create `composer.json`:

```json
{
  "name": "yourvendor/your-app-lib",
  "type": "project",
  "minimum-stability": "dev",
  "prefer-stable": true,
  "repositories": [
    {
      "type": "path",
      "url": "../../upMVC",
      "options": {
        "symlink": true
      }
    }
  ],
  "require": {
    "php": ">=8.1",
    "bitshost/upmvc": "*"
  }
}
```

Then run inside `archive/lib`:

```bash
composer install
```

This installs upMVC as a local path-based library.

## 2. Public entry point

Create a `public/` folder in `archive/lib` and copy:

- `vendor/bitshost/upmvc/public/index.php`
- `vendor/bitshost/upmvc/public/.htaccess`

So you have:

- `archive/lib/public/index.php`
- `archive/lib/public/.htaccess`

These files already point to `../vendor/autoload.php` and bootstrap upMVC.

## 3. Environment configuration (simple, current approach)

For now, the core `Environment` class expects `.env` at:

- `vendor/bitshost/upmvc/src/Etc/.env`

To configure this library instance:

1. Copy the default `.env` template from that location (if needed).
2. Edit **only** that `.env` file for this installation:
   - `DOMAIN_NAME`
   - `SITE_PATH`
   - DB settings, etc.

This is a *surgical* change inside `vendor` and is easy to re-apply after a library update.

## 4. Future: external .env loader (advanced)

The file [`Environment.php`](Environment.php) in this folder contains an **advanced** version of the `Environment` class that supports per-install `.env` files outside `vendor` via an environment variable (e.g. `UPMVC_ENV_FILE`).

We keep this version here as a **reference only**.

When we decide to move to this model globally, we can:

- Drop that enhanced `Environment.php` into `src/Etc/Config/Environment.php` in the main upMVC package.
- Update the docs to recommend per-install `.env` at the app root (e.g. `archive/lib/.env`).

For now, the official, supported workflow is section **3** above.
