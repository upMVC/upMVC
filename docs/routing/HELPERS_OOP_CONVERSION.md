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
```php
namespace upMVC;

class Helpers {
    private static ?Router $router = null;
    
    public static function setRouter(Router $router): void {
        self::$router = $router;
    }
    
    public static function route(string $name, array $params = []): string {
        return self::$router->route($name, $params);
    }
}
```

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
    Helpers::setRouter($router);
    
    // ... rest of bootstrap
}
```

### In Controllers
```php
namespace YourModule;

use upMVC\Helpers;

class Controller {
    public function show($route, $method) {
        $id = Helpers::request('id');
        $user = $this->model->find($id);
        
        Helpers::view('users/show', ['user' => $user]);
    }
    
    public function store($route, $method) {
        $data = Helpers::request();
        $user = $this->model->create($data);
        
        Helpers::redirect('user.show', ['id' => $user->id]);
    }
}
```

### In Views
```php
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="<?= \upMVC\Helpers::asset('css/style.css') ?>">
</head>
<body>
    <form method="POST" action="<?= \upMVC\Helpers::route('user.update', ['id' => $user->id]) ?>">
        <?= \upMVC\Helpers::csrfField() ?>
        <input type="text" name="name" value="<?= \upMVC\Helpers::old('name', $user->name) ?>">
        <button type="submit">Update</button>
    </form>
</body>
</html>
```

## Available Methods

All methods are static:

- `Helpers::route($name, $params)` - Generate URL from named route
- `Helpers::url($path)` - Generate full URL with BASE_URL
- `Helpers::asset($path)` - Generate asset URL
- `Helpers::redirect($to, $params, $status)` - Redirect to URL or route
- `Helpers::old($key, $default)` - Get old input value
- `Helpers::csrfToken()` - Get CSRF token
- `Helpers::csrfField()` - Generate CSRF hidden field
- `Helpers::dd(...$vars)` - Dump and die
- `Helpers::env($key, $default)` - Get environment variable
- `Helpers::config($key, $default)` - Get config value
- `Helpers::session($key, $default)` - Get session value
- `Helpers::request($key, $default)` - Get request input
- `Helpers::view($path, $data)` - Render view
- `Helpers::abort($code, $message)` - Abort with HTTP status
- `Helpers::json($data, $status)` - Return JSON response

## Testing

Run the test suite:
```bash
php zbug/test_helpers.php
```

Expected output:
```
Testing Helpers Class
=====================

✓ Helpers::setRouter() - OK
✓ Named route registered - OK
✓ Helpers::route() generated: /users/123
✓ URL generation correct - OK
✓ Helpers::csrfToken() - OK (length: 64)
✓ Helpers::csrfField() - OK

✅ All tests passed!
```

## Why OOP Over Procedural?

1. **Namespace Isolation** - No function name conflicts
2. **Dependency Injection** - Clean router injection via `setRouter()`
3. **IDE Support** - Full autocomplete and type hints
4. **Testability** - Easy to mock `Helpers::$router` in tests
5. **Consistency** - Matches upMVC's OOP architecture
6. **No Globals** - No `global $router` pollution

## Conclusion

The OOP approach provides a clean, testable, and maintainable solution that aligns perfectly with upMVC's philosophy of "Pure PHP First" while avoiding the pitfalls of global variables and procedural code.
