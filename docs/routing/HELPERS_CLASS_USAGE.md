# Helpers Class Usage Guide

## Overview

The `Helpers` class provides convenient static methods for common tasks in upMVC. It follows the "Pure PHP First" philosophy with clean OOP design.

## Initialization

The Helpers class is automatically loaded via PSR-4 autoloader and initialized in `Start.php`:

```php
// In Start.php upMVC()
HelperFacade::setRouter($router);
```

**No manual include needed!** The `App\` namespace is mapped to `src/` in `composer.json` (PSR-4 single root), so the autoloader handles it automatically.

## Usage Examples

### Routing & URLs

```php
use App\Etc\Helpers\HelperFacade;

// Generate URL from named route
$url = HelperFacade::route('user.show', ['id' => 123]);
// Returns: /users/123

// Generate full URL with BASE_URL
$fullUrl = HelperFacade::url('/api/products');
// Returns: http://localhost/upMVC/api/products

// Generate asset URL
$cssUrl = HelperFacade::asset('css/style.css');
// Returns: http://localhost/upMVC/css/style.css

// Redirect to URL or named route
HelperFacade::redirect('/dashboard');
HelperFacade::redirect('user.show', ['id' => 123]);
HelperFacade::redirect('user.show', ['id' => 123], 301); // Permanent redirect
```

### Forms & Security

```php
// Get CSRF token
$token = HelperFacade::csrfToken();

// Generate CSRF field for forms
echo HelperFacade::csrfField();
// Outputs: <input type="hidden" name="csrf_token" value="...">

// Get old input (form repopulation after validation error)
<input name="email" value="<?= HelperFacade::old('email') ?>">
```

### Session & Request

```php
// Get session value
$userId = HelperFacade::session('user_id');
$userName = HelperFacade::session('user_name', 'Guest'); // With default

// Get all session data
$allSession = HelperFacade::session();

// Get request input
$email = HelperFacade::request('email');
$search = HelperFacade::request('search', ''); // With default

// Get all request data
$allInput = HelperFacade::request();
```

### Configuration & Environment

```php
// Get environment variable
$debug = HelperFacade::env('APP_DEBUG', false);
$apiKey = HelperFacade::env('API_KEY');

// Get configuration value
$dbHost = HelperFacade::config('database.host', 'localhost');
$appName = HelperFacade::config('app.name');
```

### Views & Responses

```php
// Render a view
HelperFacade::view('users/index', ['users' => $users]);

// Return JSON response
HelperFacade::json(['success' => true, 'data' => $users]);
HelperFacade::json(['error' => 'Not found'], 404);

// Abort with HTTP status
HelperFacade::abort(404, 'Page not found');
HelperFacade::abort(403, 'Unauthorized');
```

### Debugging

```php
// Dump and die
HelperFacade::dd($user, $posts, $comments);
```

## In Controllers

```php
namespace YourModule;

use App\Etc\Helpers\HelperFacade;

class Controller
{
    public function show($route, $method)
    {
        $id = HelperFacade::request('id');
        
        if (!$id) {
            HelperFacade::abort(400, 'ID required');
        }
        
        $user = $this->model->find($id);
        
        if (!$user) {
            HelperFacade::abort(404, 'User not found');
        }
        
        HelperFacade::view('users/show', ['user' => $user]);
    }
    
    public function store($route, $method)
    {
        $data = HelperFacade::request();
        
        $user = $this->model->create($data);
        
        HelperFacade::redirect('user.show', ['id' => $user->id]);
    }
    
    public function api($route, $method)
    {
        $users = $this->model->all();
        
        HelperFacade::json([
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

## Benefits of OOP Approach

1. **Namespace Isolation**: No global function pollution
2. **Type Safety**: IDE autocomplete and type hints
3. **Testability**: Easy to mock in unit tests
4. **Extensibility**: Can be extended or overridden
5. **Pure PHP**: No magic, just clean OOP

## Migration from Procedural Functions

If you had procedural helper functions, simply add `HelperFacade::` prefix:

```php
// Old (procedural)
route('user.show', ['id' => 123]);
redirect('/dashboard');
csrf_field();

// New (OOP)
HelperFacade::route('user.show', ['id' => 123]);
HelperFacade::redirect('/dashboard');
HelperFacade::csrfField();
```

Or use `use` statement for cleaner code:

```php
use App\Etc\Helpers\HelperFacade as H;

H::route('user.show', ['id' => 123]);
H::redirect('/dashboard');
H::csrfField();
```
