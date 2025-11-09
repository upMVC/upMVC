# Router V2 Helper Functions - Usage Guide

## Two Ways to Use Helpers

Router V2 provides **TWO equivalent APIs** for maximum flexibility:

### 1. Procedural API (Global Functions) ✨

**Clean, simple, Laravel-like syntax:**

```php
// In controllers
$url = route('user.show', ['id' => 123]);
redirect('admin.dashboard');
$fullUrl = url('/admin/users');

// In views
<a href="<?= route('user.edit', ['id' => $user['id']]) ?>">Edit</a>
<form method="POST" action="<?= route('user.update', ['id' => $user['id']]) ?>">
    <?= csrf_field() ?>
    <input name="username" value="<?= old('username') ?>">
</form>
```

### 2. OOP API (Static Methods)

**Explicit, namespaced, modern PHP:**

```php
use upMVC\Helpers;

// In controllers
$url = Helpers::route('user.show', ['id' => 123]);
Helpers::redirect('admin.dashboard');
$fullUrl = Helpers::url('/admin/users');

// In views
<a href="<?= Helpers::route('user.edit', ['id' => $user['id']]) ?>">Edit</a>
```

---

## Complete Function Reference

### Routing & URLs

| Function | OOP Equivalent | Purpose | Example |
|----------|----------------|---------|---------|
| `route()` | `Helpers::route()` | Generate URL from named route | `route('user.show', ['id' => 1])` |
| `url()` | `Helpers::url()` | Full URL with BASE_URL | `url('/admin/users')` |
| `asset()` | `Helpers::asset()` | Asset URL | `asset('css/style.css')` |
| `redirect()` | `Helpers::redirect()` | Navigate to URL/route | `redirect('admin.dashboard')` |

### Forms & Security

| Function | OOP Equivalent | Purpose | Example |
|----------|----------------|---------|---------|
| `csrf_field()` | `Helpers::csrfField()` | CSRF hidden input | `<?= csrf_field() ?>` |
| `csrf_token()` | `Helpers::csrfToken()` | Get CSRF token | `csrf_token()` |
| `old()` | `Helpers::old()` | Form repopulation | `old('username')` |

### Data & Config

| Function | OOP Equivalent | Purpose | Example |
|----------|----------------|---------|---------|
| `session()` | `Helpers::session()` | Get session value | `session('user_id')` |
| `config()` | `Helpers::config()` | Get config value | `config('app.name')` |
| `env()` | `Helpers::env()` | Get env variable | `env('DB_HOST')` |

### Responses

| Function | OOP Equivalent | Purpose | Example |
|----------|----------------|---------|---------|
| `view()` | `Helpers::view()` | Render view | `view('admin.dashboard', $data)` |
| `abort()` | `Helpers::abort()` | HTTP error | `abort(404, 'Not found')` |
| `json()` | `Helpers::json()` | JSON response | `json(['success' => true])` |

### Debugging

| Function | OOP Equivalent | Purpose | Example |
|----------|----------------|---------|---------|
| `dd()` | N/A | Dump and die | `dd($user, $data)` |
| `dump()` | N/A | Dump without dying | `dump($query)` |

---

## Usage Examples

### Named Routes with Global Functions

```php
// routes/Routes.php - Define named routes
$router->addParamRoute('/users/{id:int}', User\Controller::class, 'show', [], [
    'id' => '\d+'
])->name('user.show');

$router->addParamRoute('/users/{id:int}/edit', User\Controller::class, 'edit', [], [
    'id' => '\d+'
])->name('user.edit');

// Controller.php - Generate URLs
public function index() {
    $users = $this->model->getAllUsers();
    
    foreach ($users as $user) {
        // Generate edit URL for each user
        $editUrl = route('user.edit', ['id' => $user['id']]);
        echo "Edit: $editUrl\n";
    }
}

// Redirect after save
public function update($reqRoute, $reqMet) {
    $userId = $_GET['id'];
    $this->model->update($userId, $_POST);
    
    redirect('user.show', ['id' => $userId]);
}
```

### Form with CSRF Protection

```php
<!-- View: user_form.php -->
<form method="POST" action="<?= route('user.update', ['id' => $user['id']]) ?>">
    <?= csrf_field() ?>
    
    <input type="text" name="username" value="<?= old('username', $user['username']) ?>">
    <input type="email" name="email" value="<?= old('email', $user['email']) ?>">
    
    <button type="submit">Update User</button>
</form>
```

### Navigation with Named Routes

```php
<!-- View: navigation.php -->
<nav>
    <a href="<?= route('admin.dashboard') ?>">Dashboard</a>
    <a href="<?= route('admin.users') ?>">Users</a>
    
    <?php if ($currentUser): ?>
        <a href="<?= route('user.profile', ['id' => $currentUser['id']]) ?>">
            My Profile
        </a>
    <?php endif; ?>
</nav>
```

### Debugging

```php
// Controller.php
public function debug() {
    $user = $this->model->find(1);
    $posts = $this->model->getPostsByUser(1);
    
    // Dump and die - stops execution
    dd($user, $posts);
    
    // Or just dump and continue
    dump($user);
    echo "Script continues...";
}
```

---

## Which API Should You Use?

### Use Procedural API (Global Functions) When:
- ✅ Writing views (cleaner syntax)
- ✅ Quick prototyping
- ✅ You prefer Laravel/Symfony style
- ✅ Less typing, more readability

### Use OOP API (Static Methods) When:
- ✅ You prefer explicit imports
- ✅ Working in namespaced code
- ✅ IDE autocomplete is important
- ✅ You prefer PSR style

### Both Work Perfectly! 🎉

```php
// These are IDENTICAL:
$url1 = route('user.show', ['id' => 123]);
$url2 = Helpers::route('user.show', ['id' => 123]);

// Same result, different style preference
echo $url1 === $url2; // true
```

---

## Implementation Notes

### How It Works

1. **helpers_functions.php** defines global functions
2. Each function wraps the corresponding `Helpers::` method
3. Loaded in `Start.php` before routing
4. Both APIs available throughout application

```php
// etc/helpers_functions.php
function route(string $name, array $params = []): string
{
    return Helpers::route($name, $params);
}
```

### Function Existence Check

All functions use `function_exists()` to avoid conflicts:

```php
if (!function_exists('route')) {
    function route(string $name, array $params = []): string {
        return Helpers::route($name, $params);
    }
}
```

This allows you to override or disable specific functions if needed.

---

## Migration from Helpers:: to Global Functions

### Before (OOP API):
```php
use upMVC\Helpers;

class Controller {
    public function show($reqRoute, $reqMet) {
        $userId = $_GET['id'];
        $user = $this->model->find($userId);
        
        $editUrl = Helpers::route('user.edit', ['id' => $userId]);
        $profileUrl = Helpers::url('/profile');
        
        if (!$user) {
            Helpers::redirect('users.index');
        }
        
        $data = ['user' => $user, 'editUrl' => $editUrl];
        Helpers::view('user.show', $data);
    }
}
```

### After (Procedural API):
```php
class Controller {
    public function show($reqRoute, $reqMet) {
        $userId = $_GET['id'];
        $user = $this->model->find($userId);
        
        $editUrl = route('user.edit', ['id' => $userId]);
        $profileUrl = url('/profile');
        
        if (!$user) {
            redirect('users.index');
        }
        
        $data = ['user' => $user, 'editUrl' => $editUrl];
        view('user.show', $data);
    }
}
```

**Cleaner, shorter, more readable!** ✨

---

## Router V2 Complete Feature Set

With global helper functions, Router V2 now provides:

1. ✅ **Type Casting** - `{id:int}` auto-casts
2. ✅ **Validation** - Regex constraints
3. ✅ **Named Routes** - `->name('user.show')`
4. ✅ **Route Grouping** - Auto prefix optimization
5. ✅ **Global Helpers** - `route()`, `url()`, `redirect()`
6. ✅ **OOP Helpers** - `Helpers::route()` still available

**Full Router V2 implementation complete!** 🎉
