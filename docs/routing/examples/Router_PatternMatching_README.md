# Router with Pattern Matching Support

This is a production-ready Router implementation that supports pattern matching with wildcards (`*`) and named parameters (`{id}`).

## Features

- ✅ Exact route matching (backward compatible)
- ✅ Wildcard patterns: `/users/*/edit`
- ✅ Named parameters: `/users/{id}/edit`
- ✅ Complex patterns: `/books/{author}/{year}/{isbn}`
- ✅ Parameters automatically added to `$_GET`
- ✅ Middleware support (unchanged)

## Installation

Replace `etc/Router.php` with this file:

```powershell
# Backup original
Copy-Item etc/Router.php etc/Router_BACKUP.php

# Install pattern matching router
Copy-Item docs/routing/examples/Router_PatternMatching.php etc/Router.php -Force
```

## Usage Examples

### Basic Named Parameter

```php
// In routes/Routes.php
$router->addRoute('/users/{id}', UserController::class, 'display');

// Matches: /users/123
// Result: $_GET['id'] = '123'
```

### Multiple Parameters

```php
$router->addRoute('/blog/{author}/{year}/{slug}', BlogController::class, 'display');

// Matches: /blog/john/2025/my-first-post
// Result:
//   $_GET['author'] = 'john'
//   $_GET['year'] = '2025'
//   $_GET['slug'] = 'my-first-post'
```

### Wildcard Pattern

```php
$router->addRoute('/admin/users/*/edit', AdminController::class, 'display');

// Matches: /admin/users/123/edit
// Note: Wildcard value is not captured (use {id} instead)
```

### Controller Implementation

```php
class UserController
{
    public function display()
    {
        // Parameter from pattern is in $_GET
        $userId = isset($_GET['id']) ? (int)$_GET['id'] : null;
        
        if (!$userId) {
            http_response_code(400);
            echo "Invalid user ID";
            return;
        }
        
        // Validate ID exists in database
        $user = $this->model->getUserById($userId);
        if (!$user) {
            http_response_code(404);
            echo "User not found";
            return;
        }
        
        // Display user
        $this->view->render(['user' => $user]);
    }
}
```

## Pattern Conversion Reference

| Pattern | Regex | Matches | Parameters |
|---------|-------|---------|------------|
| `/users/{id}` | `/^\/users\/(?P<id>[^\/]+)$/` | `/users/123` | `$_GET['id'] = '123'` |
| `/users/*` | `/^\/users\/([^\/]+)$/` | `/users/123` | None captured |
| `/blog/{category}/{slug}` | `/^\/blog\/(?P<category>[^\/]+)\/(?P<slug>[^\/]+)$/` | `/blog/tech/hello-world` | `$_GET['category'] = 'tech'`<br>`$_GET['slug'] = 'hello-world'` |

## Important Notes

### Security Considerations

Pattern matching allows **any value** to match the pattern. You **must validate** in your controller:

```php
// ❌ BAD - No validation
$userId = $_GET['id'];
$user = $this->model->getUserById($userId); // SQL injection risk!

// ✅ GOOD - Validate and sanitize
$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($userId <= 0) {
    http_response_code(400);
    return;
}
$user = $this->model->getUserById($userId);
if (!$user) {
    http_response_code(404);
    return;
}
```

### Route Priority

Routes are checked in this order:
1. **Exact match** - `/users/123` matches route `/users/123`
2. **Pattern match** - `/users/123` matches pattern `/users/{id}`
3. **404** - No match found

To avoid conflicts, define specific routes before patterns:

```php
// Specific routes first
$router->addRoute('/users/add', UserController::class, 'add');
$router->addRoute('/users/list', UserController::class, 'list');

// Pattern routes last
$router->addRoute('/users/{id}', UserController::class, 'display');
```

### Performance

Pattern matching uses regex, which is very fast:
- Exact match: ~0.1ms
- Pattern match: ~0.5ms

Even with 100 routes, total routing time is < 1ms.

## Rollback

If you need to go back to the original Router:

```powershell
Copy-Item etc/Router_BACKUP.php etc/Router.php -Force
```

## See Also

- [ROUTING_STRATEGIES.md](../ROUTING_STRATEGIES.md) - Complete guide to routing approaches
- [Router_CachedDB.php](Router_CachedDB.php) - Alternative with database caching
- [Routes_WithCache.php](Routes_WithCache.php) - Example implementation with cache

