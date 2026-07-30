# Helpers OOP Conversion

## Overview

Converted `helpers.php` from procedural functions to a clean OOP class following upMVC's "Pure PHP First" philosophy.

## Changes Made

### Before (Procedural - Never Implemented)
```php
// Would have required global variables
function route(string $name, array $params = []): string {
    global $router;  // ❌ Global pollution
    return $router->route($name, $params);
}
```

### After (OOP - Current Implementation)

The class lives at `src/Etc/Helpers/HelperFacade.php` and is named
**`HelperFacade`**, in namespace `App\Etc\Helpers`:

```php
namespace App\Etc\Helpers;

use App\Etc\Router;

class HelperFacade {
    private static ?Router $router = null;

    public static function setRouter(Router $router): void {
        self::$router = $router;
    }

    public static function route(string $name, array $params = []): string {
        return self::$router->route($name, $params);
    }
}
```

> **Historical note.** An intermediate version of this class was called
> `Helpers` and sat in a top-level `upMVC` namespace. Both are gone — the
> namespace root is `App\`, and the facade is `HelperFacade`. The method names
> and signatures did not change, so only the class name and import need
> updating in older code. The individual helpers it delegates to
> (`RouteHelper`, `UrlHelper`, `FormHelper`, `DataHelper`, `DebugHelper`,
> `ResponseHelper`) each live in their own file in the same folder.

## Benefits

1. **No Global Pollution** - Clean namespace isolation
2. **PSR-4 Autoloading** - No manual `require_once` needed
3. **Type Safety** - Full IDE autocomplete support
4. **Testability** - Easy to mock in unit tests
5. **Extensibility** - Can be extended or overridden
6. **Pure PHP OOP** - No magic, just clean static methods

## Integration

### In Start.php
```php
public function upMVC() {
    $router = new Router();
    
    // Initialize Helpers with router instance
    HelperFacade::setRouter($router);
    
    // ... rest of bootstrap
}
```

### In Controllers
```php
namespace App\Modules\YourModule;

use App\Etc\Helpers\HelperFacade;

class Controller {
    public function show($route, $method) {
        $id = HelperFacade::request('id');
        $user = $this->model->find($id);
        
        HelperFacade::view('users/show', ['user' => $user]);
    }
    
    public function store($route, $method) {
        $data = HelperFacade::request();
        $user = $this->model->create($data);
        
        HelperFacade::redirect('user.show', ['id' => $user->id]);
    }
}
```

### In Views
```php
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="<?= \App\Etc\Helpers\HelperFacade::asset('css/style.css') ?>">
</head>
<body>
    <form method="POST" action="<?= \App\Etc\Helpers\HelperFacade::route('user.update', ['id' => $user->id]) ?>">
        <?= \App\Etc\Helpers\HelperFacade::csrfField() ?>
        <input type="text" name="name" value="<?= \App\Etc\Helpers\HelperFacade::old('name', $user->name) ?>">
        <button type="submit">Update</button>
    </form>
</body>
</html>
```

## Available Methods

All methods are static:

- `HelperFacade::route($name, $params)` - Generate URL from named route
- `HelperFacade::url($path)` - Generate full URL with BASE_URL
- `HelperFacade::asset($path)` - Generate asset URL
- `HelperFacade::redirect($to, $params, $status)` - Redirect to URL or route
- `HelperFacade::old($key, $default)` - Get old input value
- `HelperFacade::csrfToken()` - Get CSRF token
- `HelperFacade::csrfField()` - Generate CSRF hidden field
- `HelperFacade::dd(...$vars)` - Dump and die
- `HelperFacade::env($key, $default)` - Get environment variable
- `HelperFacade::config($key, $default)` - Get config value
- `HelperFacade::session($key, $default)` - Get session value
- `HelperFacade::request($key, $default)` - Get request input
- `HelperFacade::view($path, $data)` - Render view
- `HelperFacade::abort($code, $message)` - Abort with HTTP status
- `HelperFacade::json($data, $status)` - Return JSON response

## Testing

Run the test suite:
```bash
php zbug/test_helpers.php
```

Expected output:
```
Testing Helpers Class
=====================

✓ HelperFacade::setRouter() - OK
✓ Named route registered - OK
✓ HelperFacade::route() generated: /users/123
✓ URL generation correct - OK
✓ HelperFacade::csrfToken() - OK (length: 64)
✓ HelperFacade::csrfField() - OK

✅ All tests passed!
```

## Why OOP Over Procedural?

1. **Namespace Isolation** - No function name conflicts
2. **Dependency Injection** - Clean router injection via `setRouter()`
3. **IDE Support** - Full autocomplete and type hints
4. **Testability** - Easy to mock `HelperFacade::$router` in tests
5. **Consistency** - Matches upMVC's OOP architecture
6. **No Globals** - No `global $router` pollution

## Conclusion

The OOP approach provides a clean, testable, and maintainable solution that aligns perfectly with upMVC's philosophy of "Pure PHP First" while avoiding the pitfalls of global variables and procedural code.
