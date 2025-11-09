# Helpers Class Usage Guide

## Overview

The `Helpers` class provides convenient static methods for common tasks in upMVC. It follows the "Pure PHP First" philosophy with clean OOP design.

## Initialization

The Helpers class is automatically loaded via PSR-4 autoloader and initialized in `Start.php`:

```php
// In Start.php upMVC()
Helpers::setRouter($router);
```

**No manual include needed!** The `upMVC\` namespace is mapped to `etc/` folder in `composer.json`, so PHP autoloader handles it automatically.

## Usage Examples

### Routing & URLs

```php
use upMVC\Helpers;

// Generate URL from named route
$url = Helpers::route('user.show', ['id' => 123]);
// Returns: /users/123

// Generate full URL with BASE_URL
$fullUrl = Helpers::url('/api/products');
// Returns: http://localhost/upMVC/api/products

// Generate asset URL
$cssUrl = Helpers::asset('css/style.css');
// Returns: http://localhost/upMVC/css/style.css

// Redirect to URL or named route
Helpers::redirect('/dashboard');
Helpers::redirect('user.show', ['id' => 123]);
Helpers::redirect('user.show', ['id' => 123], 301); // Permanent redirect
```

### Forms & Security

```php
// Get CSRF token
$token = Helpers::csrfToken();

// Generate CSRF field for forms
echo Helpers::csrfField();
// Outputs: <input type="hidden" name="csrf_token" value="...">

// Get old input (form repopulation after validation error)
<input name="email" value="<?= Helpers::old('email') ?>">
```

### Session & Request

```php
// Get session value
$userId = Helpers::session('user_id');
$userName = Helpers::session('user_name', 'Guest'); // With default

// Get all session data
$allSession = Helpers::session();

// Get request input
$email = Helpers::request('email');
$search = Helpers::request('search', ''); // With default

// Get all request data
$allInput = Helpers::request();
```

### Configuration & Environment

```php
// Get environment variable
$debug = Helpers::env('APP_DEBUG', false);
$apiKey = Helpers::env('API_KEY');

// Get configuration value
$dbHost = Helpers::config('database.host', 'localhost');
$appName = Helpers::config('app.name');
```

### Views & Responses

```php
// Render a view
Helpers::view('users/index', ['users' => $users]);

// Return JSON response
Helpers::json(['success' => true, 'data' => $users]);
Helpers::json(['error' => 'Not found'], 404);

// Abort with HTTP status
Helpers::abort(404, 'Page not found');
Helpers::abort(403, 'Unauthorized');
```

### Debugging

```php
// Dump and die
Helpers::dd($user, $posts, $comments);
```

## In Controllers

```php
namespace YourModule;

use upMVC\Helpers;

class Controller
{
    public function show($route, $method)
    {
        $id = Helpers::request('id');
        
        if (!$id) {
            Helpers::abort(400, 'ID required');
        }
        
        $user = $this->model->find($id);
        
        if (!$user) {
            Helpers::abort(404, 'User not found');
        }
        
        Helpers::view('users/show', ['user' => $user]);
    }
    
    public function store($route, $method)
    {
        $data = Helpers::request();
        
        $user = $this->model->create($data);
        
        Helpers::redirect('user.show', ['id' => $user->id]);
    }
    
    public function api($route, $method)
    {
        $users = $this->model->all();
        
        Helpers::json([
            'success' => true,
            'data' => $users
        ]);
    }
}
```

## In Views

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

## Benefits of OOP Approach

1. **Namespace Isolation**: No global function pollution
2. **Type Safety**: IDE autocomplete and type hints
3. **Testability**: Easy to mock in unit tests
4. **Extensibility**: Can be extended or overridden
5. **Pure PHP**: No magic, just clean OOP

## Migration from Procedural Functions

If you had procedural helper functions, simply add `Helpers::` prefix:

```php
// Old (procedural)
route('user.show', ['id' => 123]);
redirect('/dashboard');
csrf_field();

// New (OOP)
Helpers::route('user.show', ['id' => 123]);
Helpers::redirect('/dashboard');
Helpers::csrfField();
```

Or use `use` statement for cleaner code:

```php
use upMVC\Helpers as H;

H::route('user.show', ['id' => 123]);
H::redirect('/dashboard');
H::csrfField();
```
