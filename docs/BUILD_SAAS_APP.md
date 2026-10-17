# Build a multi-tenant SaaS app on upMVC (N steps)

This guide is about **how easy the floor is to set up** — kernel + saas-pack + your host app — not about shipping someone else’s domain product.

We use the **shape** of a real composed app ([myCMMS-PHP](https://github.com/BitsHost/myCMMS-PHP)) as a **structure reference**: folders, providers, module layout. We do **not** publish or copy that product’s domain code here. Your vertical (CMMS, rentals, accounting, …) stays yours.

---

## The idea in one picture

```text
bitshost/upmvc                 → kernel (boot, router, modules, JWT middleware)
bitshost/upmvc-saas-pack       → tenancy + Harvest ops (mail, notify, audit, MFA, …)
bitshost/upmvc-payments-pack   → optional payments
your-host-app/                 → only your domain modules + thin aliases / providers
```

You do **not** fork the kernel to get SaaS. You `composer require` packs and register providers.

---

## Step 1 — Create a thin host app

Either:

- start from **[upMVC-SaaS](https://github.com/upMVC/upMVC-SaaS)** (composed starter), or  
- `composer create-project bitshost/upmvc my-saas` and add the pack yourself.

Host tree stays small on purpose:

```text
my-saas/
├── public/
│   └── index.php          # defines UPMVC_APP_ROOT, boots kernel
├── src/
│   ├── Etc/
│   │   ├── .env           # DOMAIN_NAME, SITE_PATH, DB_*, JWT_SECRET, …
│   │   └── packages.php   # register SaaS (and optional) providers
│   └── Modules/           # your domain (+ optional web shells)
├── database/              # app migrations only (if any)
├── composer.json          # require upmvc + upmvc-saas-pack
└── vendor/                # packs land here
```

---

## Step 2 — Require the floor (Composer)

```bash
composer require bitshost/upmvc:^2.5 bitshost/upmvc-saas-pack:^1.1
# optional later:
# composer require bitshost/upmvc-payments-pack
```

`composer.json` (host) stays declarative — no copied kernel sources.

---

## Step 3 — Register providers

```php
// src/Etc/packages.php
return [
    \BitsHost\UpmvcSaas\SaasServiceProvider::class,
    // \BitsHost\UpmvcPayments\PaymentsServiceProvider::class,
    // \App\MyProduct\MyProductSaasProvider::class,  // your boot hooks (notify types, …)
];
```

The saas-pack provider registers:

- pack module path (Auth, Tenants, Plans, Admin, Harvest APIs, web shells)
- migrations `001` / `002`
- `tenant` middleware + `feature:*` factory
- protected route prefixes for platform admin / app shell

---

## Step 4 — Configure `.env` and migrate

Set at least: `DOMAIN_NAME`, `SITE_PATH`, `DB_*`, `APP_KEY`, `JWT_SECRET`, `APP_ENV`.

Then run the host migrate tool (kernel → pack → app order):

```bash
php vendor/bitshost/upmvc/src/Tools/migrate.php
```

You get users (kernel) + plans/tenants/refresh tokens (saas `001`) + ops tables (Harvest `002`) without inventing that schema yourself.

Seed demo tenants/users from the pack’s seed when you want a quick login path — see pack `database/README.md`.

---

## Step 5 — Understand what already works

Out of the box (from the pack, not your domain):

| Area | You get |
|------|---------|
| Auth API | Login / refresh / logout (JWT) |
| Tenants / plans | Register, plan list, feature gates |
| Platform admin | Tenant/plan admin APIs + web shell |
| Harvest ops | Mail gateway, notify primitives, audit, TOTP, Attention, i18n overlays |

Your first “hello SaaS” is: configure DB → migrate → `POST /api/auth/login` → call a tenant-scoped route with `Authorization: Bearer …`.

---

## Step 6 — Add **your** domain as modules

Convention the kernel discovers automatically:

```text
src/Modules/Api/Modules/{Domain}/
├── Controller.php      # typically extends SaasApiController
├── Model.php           # scope every query with tenant_id
└── Routes/Routes.php   # addRoute(..., ['cors', 'jwt'], ['GET'])
```

**Depth matters:** the folder must be `Api/Modules/{Domain}/` (literal `Modules` segment). Shallower trees are not discovered.

Web shells (optional) live beside APIs, e.g. `src/Modules/TenantApp/`, `src/Modules/PlatformAdmin/` — or reuse the pack’s shells and only override what you need.

### Illustrative host layout (structure only)

This mirrors how a composed product organizes folders — **names are examples**, not a dump of product source:

```text
src/
├── Etc/
│   ├── .env
│   └── packages.php
├── {Product}/                    # optional app library (thin aliases, providers)
│   ├── {Product}SaasProvider.php # e.g. register NotifyRegistry types at boot
│   └── …                         # thin extends of pack classes if you want App\… names
└── Modules/
    ├── Api/Modules/
    │   ├── Auth/                 # often from pack — override only if needed
    │   ├── Tenants/ Plans/ …
    │   ├── PlatformOps/ I18n/    # Harvest thin APIs — override for domain Attention
    │   └── {YourDomain}/         # ← you build these (Bookings, Assets, Invoices, …)
    ├── Auth/                     # session web login shell (pack or yours)
    ├── PlatformAdmin/
    └── TenantApp/
```

**Rule:** if car-rental and accounting both need it before domain logic → pack. Vertical-only logic → host `Modules/`.

---

## Step 7 — Keep boundaries (your moat)

| Put here | Do not put here |
|----------|-----------------|
| saas-pack | Tenancy, JWT refresh, Harvest ops | Product price rules, WO/PM catalogs, rental seasons |
| payments-pack | Gateways, `payment_intents` | “What to charge for” |
| host app | Domain modules, notify type catalogs, UI | Copied kernel / copied saas-pack sources |

That is how you avoid forking everything again (the old path many homemade SaaS apps took).

---

## Optional — payments later

```bash
composer require bitshost/upmvc-payments-pack
# register PaymentsServiceProvider
# PAYMENTS_DRIVER=manual|cash|bank_transfer|stripe
```

Same pattern: require → register → migrate → call thin API / library.

---

## Helper CLI (after saas-pack is required)

```bash
php vendor/bitshost/upmvc-saas-pack/bin/saas-next.php doctor
php vendor/bitshost/upmvc-saas-pack/bin/saas-next.php init
php vendor/bitshost/upmvc-saas-pack/bin/saas-next.php scaffold-domain YourDomain
php vendor/bitshost/upmvc-saas-pack/bin/saas-next.php agent --goal "…"
```

Discovers optional packs (e.g. payments) via each package’s `saas-kit.json`. See saas-pack **[docs/SAAS_NEXT.md](https://github.com/upMVC/upMVC-SaaS-Pack/blob/master/docs/SAAS_NEXT.md)**.

## Checklist

- [ ] Host `public/index.php` sets `UPMVC_APP_ROOT`
- [ ] `composer require` kernel + saas-pack (not a kernel fork)
- [ ] `packages.php` registers `SaasServiceProvider`
- [ ] `.env` has domain, DB, JWT
- [ ] Migrations applied
- [ ] `saas-next doctor` is clean
- [ ] JWT login works
- [ ] First domain module under `src/Modules/Api/Modules/{Domain}/`
- [ ] Every tenant query uses `tenant_id`

---

## Further reading

| Doc | Why |
|-----|-----|
| [KERNEL.md](KERNEL.md) | What the kernel actually is |
| [MODULE_PHILOSOPHY.md](MODULE_PHILOSOPHY.md) | Modules as the unit of work |
| [saas-pack README](https://github.com/upMVC/upMVC-SaaS-Pack) | Pack install + Harvest |
| [saas-next CLI](https://github.com/upMVC/upMVC-SaaS-Pack/blob/master/docs/SAAS_NEXT.md) | Host doctor / init / agent / scaffold |
| [saas-pack docs/09](https://github.com/upMVC/upMVC-SaaS-Pack/blob/master/docs/09-harvest-ops.md) | Mail / notify / dual-unseal |
| [INTEGRATION_PHP_CRUD_API.md](INTEGRATION_PHP_CRUD_API.md) | Optional API plane |
| Agent: `docs/agent/upmvc-saas-pack.json` | Machine knowledge when the pack is present |

**Structure reference (public repo, your product code stays yours):** [myCMMS-PHP](https://github.com/BitsHost/myCMMS-PHP) — composed host on Packagist pins; use it to see folder/provider/module layout, not to copy domain internals into docs.
