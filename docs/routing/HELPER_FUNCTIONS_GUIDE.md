# Router V2 Helpers — Usage Guide

> **Note on global functions.** Earlier drafts of this guide described a
> procedural API — bare `route()`, `url()`, `csrf_field()` and friends — backed
> by a `src/Etc/helpers_functions.php` loaded from `Start.php`. **That file was
> never written and those functions do not exist.** Calling `route(...)` today
> raises `Call to undefined function route()`. upMVC has no `files` entry in
> `composer.json`, so nothing defines global functions at boot.
>
> The static facade below is the real, working API.

## The API: `HelperFacade`

```php
use App\Etc\Helpers\HelperFacade;

// In controllers
$url     = HelperFacade::route('user.show', ['id' => 123]);
$fullUrl = HelperFacade::url('/admin/users');
HelperFacade::redirect('admin.dashboard');

// In views
<a href="<?= HelperFacade::route('user.edit', ['id' => $user['id']]) ?>">Edit</a>
```

The class lives at `src/Etc/Helpers/HelperFacade.php`. It is a thin facade over
the individual helper classes in the same folder — `RouteHelper`, `UrlHelper`,
`FormHelper`, `DataHelper`, `DebugHelper`, `ResponseHelper` — which you can also
use directly if you prefer.

`HelperFacade::route()` needs the router instance, which `Start.php` injects
during bootstrap via `HelperFacade::setRouter($router)`.

---

## Complete Method Reference

### Routing & URLs

| Method | Purpose | Example |
|---|---|---|
| `HelperFacade::route()` | Generate URL from named route | `HelperFacade::route('user.show', ['id' => 1])` |
| `HelperFacade::url()` | Full URL with BASE_URL | `HelperFacade::url('/admin/users')` |
| `HelperFacade::asset()` | Asset URL | `HelperFacade::asset('css/style.css')` |
| `HelperFacade::redirect()` | Navigate to URL/route | `HelperFacade::redirect('admin.dashboard')` |

### Forms & Security

| Method | Purpose | Example |
|---|---|---|
| `HelperFacade::csrfField()` | CSRF hidden input | `<?= HelperFacade::csrfField() ?>` |
| `HelperFacade::csrfToken()` | Get CSRF token | `HelperFacade::csrfToken()` |
| `HelperFacade::old()` | Form repopulation | `HelperFacade::old('username')` |

### Data & Config

| Method | Purpose | Example |
|---|---|---|
| `HelperFacade::session()` | Get session value | `HelperFacade::session('user_id')` |
| `HelperFacade::config()` | Get config value | `HelperFacade::config('app.name')` |
| `HelperFacade::env()` | Get env variable | `HelperFacade::env('DB_HOST')` |
| `HelperFacade::request()` | Get request input | `HelperFacade::request('id')` |

### Responses

| Method | Purpose | Example |
|---|---|---|
| `HelperFacade::view()` | Render view | `HelperFacade::view('admin.dashboard', $data)` |
| `HelperFacade::abort()` | HTTP error | `HelperFacade::abort(404, 'Not found')` |
| `HelperFacade::json()` | JSON response | `HelperFacade::json(['success' => true])` |

### Debugging

| Method | Purpose | Example |
|---|---|---|
| `HelperFacade::dd()` | Dump and die | `HelperFacade::dd($user, $data)` |
| `HelperFacade::dump()` | Dump without dying | `HelperFacade::dump($query)` |

---

## Usage Examples

### Named Routes

Only `addParamRoute()` is chainable — it returns `$this`. `addRoute()` returns
nothing, so `addRoute(...)->name(...)` is a fatal error.

```php
// Routes/Routes.php — define named routes
$router->addParamRoute('/users/{id:int}', User\Controller::class, 'show', [], [
    'id' => '\d+'
])->name('user.show');

$router->addParamRoute('/users/{id:int}/edit', User\Controller::class, 'edit', [], [
    'id' => '\d+'
])->name('user.edit');
```

```php
// Controller.php — generate URLs
use App\Etc\Helpers\HelperFacade;

public function index() {
    $users = $this->model->getAllUsers();

    foreach ($users as $user) {
        $editUrl = HelperFacade::route('user.edit', ['id' => $user['id']]);
        echo "Edit: $editUrl\n";
    }
}

// Redirect after save
public function update($reqRoute, $reqMet) {
    $userId = $_GET['id'];
    $this->model->update($userId, $_POST);

    HelperFacade::redirect('user.show', ['id' => $userId]);
}
```

### Form with CSRF Protection

```php
<!-- View: user_form.php -->
<?php use App\Etc\Helpers\HelperFacade; ?>

<form method="POST" action="<?= HelperFacade::route('user.update', ['id' => $user['id']]) ?>">
    <?= HelperFacade::csrfField() ?>

    <input type="text" name="username" value="<?= HelperFacade::old('username', $user['username']) ?>">
    <input type="email" name="email" value="<?= HelperFacade::old('email', $user['email']) ?>">

    <button type="submit">Update User</button>
</form>
```

### Navigation with Named Routes

```php
<!-- View: navigation.php -->
<?php use App\Etc\Helpers\HelperFacade; ?>

<nav>
    <a href="<?= HelperFacade::route('admin.dashboard') ?>">Dashboard</a>
    <a href="<?= HelperFacade::route('admin.users') ?>">Users</a>

    <?php if ($currentUser): ?>
        <a href="<?= HelperFacade::route('user.profile', ['id' => $currentUser['id']]) ?>">
            My Profile
        </a>
    <?php endif; ?>
</nav>
```

### Debugging

```php
use App\Etc\Helpers\HelperFacade;

public function debug() {
    $user  = $this->model->find(1);
    $posts = $this->model->getPostsByUser(1);

    HelperFacade::dd($user, $posts);      // dump and stop

    HelperFacade::dump($user);            // dump and continue
    echo "Script continues...";
}
```

---

## Shortening the Call Site

If `HelperFacade::` is too long for your taste, alias it on import — this is
plain PHP and needs no framework support:

```php
use App\Etc\Helpers\HelperFacade as H;

$url = H::route('user.show', ['id' => 123]);
```

Or import the specific helper you need:

```php
use App\Etc\Helpers\RouteHelper;

$url = RouteHelper::route('user.show', ['id' => 123]);
```

---

## Router V2 Feature Set

1. ✅ **Type Casting** — `{id:int}`, `{price:float}`, `{active:bool}` auto-cast
2. ✅ **Validation** — regex constraints as the 5th argument to `addParamRoute()`
3. ✅ **Named Routes** — `addParamRoute(...)->name('user.show')`
4. ✅ **Prefix Grouping** — routes are bucketed by first segment internally to
   speed up matching. This is an optimisation, not a `group()` API — no such
   method exists.
5. ✅ **Static Helpers** — `HelperFacade::route()` and friends
6. ❌ **Global Functions** — not implemented; see the note at the top
