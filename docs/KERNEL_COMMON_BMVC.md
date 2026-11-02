# Kernel, Common, BMVC — what you get (simple / non-SaaS)

This guide is for **plain upMVC** — standalone or as a Composer library — **without** `upmvc-saas-pack`.  
No `tenant_id`, no Harvest. SaaS builds on the same ideas later ([BUILD_SAAS_APP](BUILD_SAAS_APP.md)).

---

## Three layers (do not mix them up)

```text
src/Etc/           → KERNEL (product). Boot, router, config, middleware, tools.
src/Common/Bmvc/   → SHARED APP SKELETON (design). BaseModel / BaseView / BaseController / …
src/Modules/       → YOUR FEATURES (+ optional demos zip). Take / try / drop.
```

| Layer | Is it the kernel? | Can you delete it like demos? | Role |
|-------|-------------------|-------------------------------|------|
| **`src/Etc/`** | **Yes** | No — this *is* upMVC | Framework runtime |
| **`src/Common/Bmvc/`** | **No** | **No** (not “optional bloat”) | Shared bases for models/views/controllers — the common skeleton |
| **`src/Modules/*` demos** | No | **Yes** — Releases zip | Reference implementations |
| **Welcome** | No | Replace via `custom-routes.php` | Stock homepage; **does not** extend BMVC |

**Modules ≠ Common.**  
We pulled demos out of the stock tree so create-project stays thin. That trick **does not** apply to `/Common`: if we shipped without BMVC, newcomers would shove layout, PDO helpers, and HTML shell into every module and never discover a shared skeleton.

BMVC is **optional to use** (Welcome proves that) but **part of the architecture** (present so you *can* share a skeleton). You may extend, override, or ignore it — you should not pretend it is “junk you pull later.”

Kernel surface (claims from running code): **[KERNEL.md](KERNEL.md)**.

---

## What is `/Common` and BMVC?

**`src/Common/`** — place for code shared across modules (not feature folders).

**`src/Common/Bmvc/`** ships starter bases:

| Class | Use when |
|-------|----------|
| `BaseModel` | PDO + simple CRUD helpers |
| `BaseController` | HTML/session controllers (params, validate, sanitize) |
| `BaseApiController` | JSON APIs (`success` / `error` / `requireFields`) |
| `BaseView` | Small HTML shell (default for new `AppView`) |
| `BaseViewModern` | Heavier modern shell — only if you want that look |
| `Base*Orm` | Optional RedBean path (`composer suggest`) |

They are **conventions / starters**, not a mandatory inheritance tree. Stock **Welcome** extends nothing from BMVC.

Recommended host pattern (library or standalone):

```text
App\Common\Bmvc\BaseModel|BaseView|…
        ↑
App\Common\AppModel / AppView     ← your app skeleton (brand, helpers)
        ↑
App\Modules\Notes\Model|View      ← feature modules
```

Pick **one** view base for `AppView` (`BaseView` recommended as default; `BaseViewModern` if you prefer it). You may define as many app shells as you need (`AppView`, `AppViewAdmin`, …).

---

## Mode A — Standalone (`create-project`)

```bash
composer create-project bitshost/upmvc my-app
```

| Path | After install | `composer update` |
|------|---------------|-------------------|
| `src/Etc/`, `src/Common/`, `src/Modules/` | **Your app tree** (copied into the project) | Updates **vendor only** (e.g. PHPMailer) — **does not** rewrite `src/Common/Bmvc` |
| Kernel upgrades from GitHub | Manual merge / careful copy | Same risk as any app that forked sources |

**You may edit `src/Common/Bmvc` directly** in standalone — it is your copy.  
Risk is **not** Composer; risk is **re-copying an upstream release over your tree** or `git pull` from the upMVC repo if you develop on a clone of upstream.

---

## Mode B — Library (`composer require`)

```bash
composer require bitshost/upmvc:^2.5
```

Host owns `public/`, `src/Etc/.env`, `packages.php` (optional), `src/Modules/`.  
Kernel + BMVC live under `vendor/bitshost/upmvc/`.

Typical PSR-4 (host first):

```text
App\ → [ host/src , vendor/bitshost/upmvc/src ]
```

| Do | Don’t |
|----|--------|
| `extends` BMVC from host (`AppModel`, `AppView`, module classes) | Edit `vendor/.../Bmvc` — **wiped** on `composer update` |
| Optional: same FQCN under `host/src/Common/Bmvc/` to shadow vendor | Put `.env` only inside vendor (use host `UPMVC_APP_ROOT` + `src/Etc/.env`) |

Define `UPMVC_APP_ROOT` in the host `public/index.php` **before** autoload (see [KERNEL.md](KERNEL.md)).

---

## Examples (library, non-SaaS)

### AppModel → module Model

```php
<?php
// host: src/Common/AppModel.php
namespace App\Common;

use App\Common\Bmvc\BaseModel;

class AppModel extends BaseModel
{
    // shared helpers for this app
}
```

```php
<?php
// host: src/Modules/Notes/Model.php
namespace App\Modules\Notes;

use App\Common\AppModel;

class Model extends AppModel
{
    public function all(): array
    {
        return $this->conn
            ->query('SELECT id, title FROM notes ORDER BY id DESC')
            ->fetchAll(\PDO::FETCH_ASSOC);
    }
}
```

### AppView → module View

```php
<?php
// host: src/Common/AppView.php
namespace App\Common;

use App\Common\Bmvc\BaseView;

class AppView extends BaseView
{
    // override menu() / shell when you outgrow the starter nav
}
```

```php
<?php
// host: src/Modules/Notes/View.php
namespace App\Modules\Notes;

use App\Common\AppView;

class View extends AppView
{
    public function list(array $notes): void
    {
        $this->startHead('Notes');
        $this->endHead();
        $this->startBody('Notes');
        // …
        $this->endBody();
        $this->startFooter();
        $this->endFooter();
    }
}
```

### JSON without a View

```php
use App\Common\Bmvc\BaseApiController;

class Controller extends BaseApiController
{
    public function index($reqRoute, $reqMet): never
    {
        $this->success((new Model())->all());
    }
}
```

Or a plain controller with no BMVC at all (same idea as Welcome).

---

## Update / “migration” scenarios

| Situation | What happens | What you do |
|-----------|--------------|-------------|
| Standalone + `composer update` | `src/Common` untouched | Keep working |
| Standalone + paste new upMVC zip over project | Can overwrite `src/Etc` **and** `src/Common` | Diff/merge; or treat `Common` as yours and restore |
| Library + `composer update bitshost/upmvc` | New kernel + stock BMVC in **vendor** | Host `App*` / modules stay; re-test extends |
| Library + edited files under `vendor/.../Bmvc` | **Lost** | Move logic to host `AppView` / `AppModel` |
| Want DB schema changes | Unrelated to BMVC | `php vendor/bitshost/upmvc/src/Tools/migrate.php` (or standalone `php src/Tools/migrate.php`) — see `database/README.md` |

---

## Mental checklist

1. Need a feature? → **module** under `src/Modules/…`  
2. Need shared layout / PDO helpers for many modules? → **`App*` in host Common**, extending BMVC  
3. Need framework behaviour (routes, JWT named middleware, boot)? → **kernel `src/Etc`** — don’t fork it in library mode  
4. Optional demos? → Releases zip — not required for Common to exist  

**SaaS later:** same extend pattern; pack adds tenancy on top. Learn the simple path first.

---

## Related

- [KERNEL.md](KERNEL.md) — boot, config, router  
- [MODULE_PHILOSOPHY.md](MODULE_PHILOSOPHY.md) — demos as references  
- [FIRST-STEPS-GUIDE.md](FIRST-STEPS-GUIDE.md) — get running  
- [BUILD_SAAS_APP.md](BUILD_SAAS_APP.md) — only when you add saas-pack  
