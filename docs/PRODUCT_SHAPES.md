# Product shapes — two simple ways to build on upMVC

Stock upMVC does not force a layout. For a **lean host** (no SaaS pack required), two general patterns cover most apps. Pick one — or mix later. Neither is mandatory.

Agents may **propose** these options on greenfield work (`product_shapes` / `simple_product_shape`). You still decide.

---

## At a glance

| | **Web thin modules** | **API-first + shells** |
|--|----------------------|-------------------------|
| **Idea** | Each feature is a module that renders HTML (or a little JS) | JSON APIs own the domain; thin pages/shells only load UI |
| **Folders** | `src/Modules/{Domain}/` — Controller, Model, View, `Routes/` | `src/Modules/Api/Modules/{Domain}/` + optional `Site` / `AdminApp` / … |
| **Auth** | Session, `PROTECTED_ROUTES`, `['auth']` / `['csrf']` | JWT — `JwtService` + route middleware `['cors', 'jwt']` |
| **Homepage** | `src/Etc/custom-routes.php` → one module owns `/` | Public shell module + APIs under `/api/...` |
| **Good for** | Catalogs, shops, portals, admin with server-rendered pages | SPA admin, mobile clients, separate frontend repo |
| **Kernel helpers** | `BaseController`, `BaseModel`, `BaseView`, `Security` | `BaseApiController`, `JwtService`, named `jwt` / `cors` |

SaaS / payments packs are **optional later** — when you need multi-tenant or a shared payment floor. Until then, Composer SDKs (Stripe, Netopia, …) can live in the app.

Related: [KERNEL_COMMON_BMVC.md](KERNEL_COMMON_BMVC.md) (kernel vs Common vs modules), [BUILD_SAAS_APP.md](BUILD_SAAS_APP.md) (when you *do* compose SaaS).

---

## Shape A — Web thin modules

### When

You want pages the server can render: product list, cart, simple admin, forms. One concern per module. No SPA required.

### Layout

```text
src/Modules/
  Catalog/     # list + detail
  Shop/        # cart, checkout
  Admin/       # back-office pages
  Cont/        # optional account area
```

Each module typically has:

- `Controller.php`, `Model.php`, `View.php`
- `Routes/Routes.php` — discovered automatically

### How to start

1. Create-project / clone stock upMVC (Welcome homepage is fine).
2. Add a domain module under `src/Modules/{Name}/` with `Routes/Routes.php`.
3. Point `/` in `src/Etc/custom-routes.php` at your main module when ready.
4. Session areas: set `PROTECTED_ROUTES` in `.env` and/or use `['csrf']` / `['auth']` on routes.
5. Schema: new file under `database/migrations/` → `php src/Tools/migrate.php`.

### Tips

- Prefer kernel `Security::rateLimit()` / BMVC bases over inventing parallel helpers.
- Keep modules thin: catalog ≠ checkout ≠ admin.

---

## Shape B — API-first + shells

### When

A React/Vue/mobile client (or several UIs) should talk JSON. The PHP app is primarily an API; HTML modules are thin shells that serve the SPA or a public landing page.

### Layout

```text
src/Modules/
  Api/
    Modules/
      Auth/           # login / refresh — JWT
      Products/       # domain APIs
      Orders/
  Site/               # optional public HTML shell
  AdminApp/           # optional admin SPA shell
```

**Discovery trap:** nested APIs need the literal folder segment `Modules`:

- Correct: `src/Modules/Api/Modules/{Domain}/Routes/Routes.php`
- Wrong: `src/Modules/Api/{Domain}/` — **never discovered**

### How to start

1. Stock upMVC + `JWT_SECRET` in `.env`.
2. Add domains under `src/Modules/Api/Modules/{Domain}/`.
3. Controllers: extend `App\Common\Bmvc\BaseApiController` (JSON helpers) — do not invent a parallel `ApiResponse` class by default.
4. Protected routes: `addRoute(..., ['cors', 'jwt'], ['GET','POST', …])`.
5. Issue tokens with `App\Etc\JwtService` (prefer this over adding a third-party JWT library unless you have a reason).
6. Optional shells (`Site`, `AdminApp`, …) only serve HTML/JS; they call your `/api/...` endpoints.

### Tips

- Stay single-tenant on the stock kernel until you explicitly want `bitshost/upmvc-saas-pack`.
- Same migration discipline as shape A.

---

## How you use this with an AI agent

You do **not** have to run a special CLI for every chat. In Cursor / Claude Code, root [`AGENTS.md`](../AGENTS.md) already points at the agent pack.

| You say | What happens |
|---------|----------------|
| „Adaugă modulul Contact” / “Add a Contact module” | Short path: create one module (`create_module`) |
| „Cum structurez un shop lean?” / “API + SPA admin — propose a layout” | Agent may **propose** these two shapes; you choose |
| You want a pasteable brief | Optional: `php src/Tools/upmvc-next.php --goal "…"` → paste `docs/agent/generated/last-prompt.md` |

Full agent how-to: **[AGENT_PACK.md](AGENT_PACK.md)** · README section [AI Agent](../README.md#-ai-agent--built-in-context-for-cursor-claude-and-local-llms).

---

## What not to do

- Do not treat demos zip modules as the kernel API.
- Do not skip the `Modules` segment under `Api/` for JSON domains.
- Do not pull saas-pack “just in case” for a single-tenant lean app.
- Do not name-drop unrelated product repos as requirements — these shapes are **general**.
