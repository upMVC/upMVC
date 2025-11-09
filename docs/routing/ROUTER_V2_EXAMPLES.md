# Router v2.0 - Usage Examples

Complete examples demonstrating all 4 enhancements in real-world scenarios.

---

## 🎯 Enhancement 1: Validation Patterns

### Basic Numeric Validation

```php
// Only accept numeric IDs
$router->addParamRoute('/users/{id}', User\Controller::class, 'show', [], [
    'id' => '\d+'
]);

// ✅ /users/123 - Matches
// ❌ /users/abc - 404 (rejected at router level)
```

### UUID Validation

```php
$router->addParamRoute('/orders/{uuid}', Order\Controller::class, 'show', [], [
    'uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}'
]);

// ✅ /orders/550e8400-e29b-41d4-a716-446655440000
// ❌ /orders/invalid-uuid
```

### Date Validation

```php
$router->addParamRoute('/archive/{year}/{month}', Archive\Controller::class, 'show', [], [
    'year' => '\d{4}',
    'month' => '(0[1-9]|1[0-2])'
]);

// ✅ /archive/2024/01
// ❌ /archive/24/13
```

---

## 🎯 Enhancement 2: Type Casting

### Integer Parameters

```php
$router->addParamRoute('/users/{id:int}', User\Controller::class, 'show');

// Controller receives:
public function show($reqRoute, $reqMet)
{
    $userId = $_GET['id']; // int(123) - already casted!
    
    // No need for: $userId = (int)$_GET['id'];
}
```

### Float Parameters

```php
$router->addParamRoute('/products/filter/{minPrice:float}/{maxPrice:float}', 
    Product\Controller::class, 'filter');

// Controller:
public function filter($reqRoute, $reqMet)
{
    $min = $_GET['minPrice'];  // float(19.99)
    $max = $_GET['maxPrice'];  // float(99.99)
}
```

### Boolean Parameters

```php
$router->addParamRoute('/settings/{active:bool}', Settings\Controller::class, 'toggle');

// Controller:
public function toggle($reqRoute, $reqMet)
{
    $active = $_GET['active']; // bool(true) or bool(false)
}
```

### Mixed Types

```php
$router->addParamRoute(
    '/orders/{orderId:int}/items/{itemId:int}/quantity/{qty:float}',
    OrderItem\Controller::class,
    'update'
);

// All parameters auto-casted to correct types
```

---

## 🎯 Enhancement 3: Route Grouping (Automatic)

This enhancement works automatically - no code changes needed!

```php
// Register 100 routes
for ($i = 0; $i < 100; $i++) {
    $router->addParamRoute("/category{$i}/{id}", Controller::class, 'show');
}

// Router automatically groups by prefix:
// 'category0' => [routes...]
// 'category1' => [routes...]
// etc.

// Request: /category50/123
// Only checks 'category50' group instead of all 100 routes
// Result: 50x faster matching!
```

---

## 🎯 Enhancement 4: Named Routes

### Basic Named Routes

```php
// Register with name
$router->addParamRoute('/users/{id}', User\Controller::class, 'show')
    ->name('user.show');

$router->addParamRoute('/users/{id}/edit', User\Controller::class, 'edit')
    ->name('user.edit');

// Generate URLs
$url = route('user.show', ['id' => 123]);  // /users/123
$url = route('user.edit', ['id' => 123]);  // /users/123/edit
```

### In Views

```php
<!-- Old way (brittle) -->
<a href="/users/edit/<?= $user['id'] ?>">Edit</a>

<!-- New way (refactor-safe) -->
<a href="<?= route('user.edit', ['id' => $user['id']]) ?>">Edit</a>
```

### In Controllers

```php
public function create($reqRoute, $reqMet)
{
    $userId = $this->model->createUser($_POST);
    
    // Old way
    // header('Location: /users/' . $userId);
    
    // New way
    redirect('user.show', ['id' => $userId]);
}
```

### Complex Routes

```php
$router->addParamRoute(
    '/blog/{year}/{month}/{slug}',
    Blog\Controller::class,
    'show'
)->name('blog.post');

// Generate URL
$url = route('blog.post', [
    'year' => 2024,
    'month' => '01',
    'slug' => 'hello-world'
]);
// Result: /blog/2024/01/hello-world
```

---

## 🔥 Real-World Complete Example

### E-commerce Product Module

```php
// routes/Routes.php
namespace Product\Routes;

use Product\Controller;

class Routes
{
    public function routes($router)
    {
        // List products
        $router->addRoute('/products', Controller::class, 'index');
        
        // Show product (with validation + type casting + named route)
        $router->addParamRoute(
            '/products/{id:int}',
            Controller::class,
            'show',
            [],
            ['id' => '\d+']  // Validation: only numbers
        )->name('product.show');
        
        // Filter by price range
        $router->addParamRoute(
            '/products/filter/{minPrice:float}/{maxPrice:float}',
            Controller::class,
            'filter',
            [],
            [
                'minPrice' => '\d+(\.\d{1,2})?',
                'maxPrice' => '\d+(\.\d{1,2})?'
            ]
        )->name('product.filter');
        
        // Category products
        $router->addParamRoute(
            '/products/category/{slug}',
            Controller::class,
            'category',
            [],
            ['slug' => '[a-z0-9-]+']
        )->name('product.category');
    }
}
```

### Controller

```php
namespace Product;

class Controller
{
    public function show($reqRoute, $reqMet)
    {
        // $id is already int, already validated
        $productId = $_GET['id'];
        
        $product = $this->model->getById($productId);
        
        if (!$product) {
            abort(404, 'Product not found');
        }
        
        $this->view->render(['product' => $product]);
    }
    
    public function filter($reqRoute, $reqMet)
    {
        // Already float, already validated
        $minPrice = $_GET['minPrice'];
        $maxPrice = $_GET['maxPrice'];
        
        $products = $this->model->filterByPrice($minPrice, $maxPrice);
        
        $this->view->render(['products' => $products]);
    }
}
```

### View

```php
<!-- Product list -->
<?php foreach ($products as $product): ?>
    <div class="product">
        <h3><?= $product['name'] ?></h3>
        <p>$<?= $product['price'] ?></p>
        
        <!-- Using named routes -->
        <a href="<?= route('product.show', ['id' => $product['id']]) ?>">
            View Details
        </a>
    </div>
<?php endforeach; ?>

<!-- Filter link -->
<a href="<?= route('product.filter', ['minPrice' => 10, 'maxPrice' => 100]) ?>">
    $10 - $100
</a>
```

---

## 🎨 Admin Module Example

```php
// Admin routes with all enhancements
$router->addRoute('/admin', Admin\Controller::class, 'dashboard');
$router->addRoute('/admin/users', Admin\Controller::class, 'listUsers');

// Edit user (validation + type + name)
$router->addParamRoute(
    '/admin/users/edit/{id:int}',
    Admin\Controller::class, 
    'editUser',
    [],
    ['id' => '\d+']
)->name('admin.user.edit');

// Delete user
$router->addParamRoute(
    '/admin/users/delete/{id:int}',
    Admin\Controller::class,
    'deleteUser',
    [],
    ['id' => '\d+']
)->name('admin.user.delete');

// Controller
public function editUser($reqRoute, $reqMet)
{
    $userId = $_GET['id']; // int, validated
    
    if ($reqMet === 'POST') {
        $this->model->updateUser($userId, $_POST);
        redirect('admin.users');
    }
    
    $user = $this->model->getUserById($userId);
    $this->view->renderEditForm($user);
}

// View
<form method="POST" action="<?= route('admin.user.edit', ['id' => $user['id']]) ?>">
    <?= csrf_field() ?>
    <input name="username" value="<?= $user['username'] ?>">
    <button type="submit">Update</button>
</form>

<a href="<?= route('admin.user.delete', ['id' => $user['id']]) ?>" 
   onclick="return confirm('Delete user?')">
    Delete
</a>
```

---

## 🚀 Migration from v1.0 to v2.0

### Before (v1.0)

```php
// Routes
$router->addParamRoute('/users/{id}', Controller::class, 'show');

// Controller
public function show($reqRoute, $reqMet)
{
    $id = $_GET['id'] ?? null;
    
    // Manual validation
    if (!$id || !ctype_digit($id)) {
        abort(400, 'Invalid ID');
    }
    
    // Manual casting
    $userId = (int)$id;
    
    $user = $this->model->getById($userId);
}

// View
<a href="/users/<?= $user['id'] ?>">View</a>
```

### After (v2.0)

```php
// Routes
$router->addParamRoute(
    '/users/{id:int}',
    Controller::class,
    'show',
    [],
    ['id' => '\d+']
)->name('user.show');

// Controller
public function show($reqRoute, $reqMet)
{
    // Already validated and casted!
    $userId = $_GET['id'];
    
    $user = $this->model->getById($userId);
}

// View
<a href="<?= route('user.show', ['id' => $user['id']]) ?>">View</a>
```

**Benefits:**
- ✅ Less code in controller
- ✅ Validation at router level
- ✅ Auto type casting
- ✅ Refactor-safe URLs

---

## 📚 Helper Functions

All available in `etc/helpers.php`:

```php
// Named route URL
route('user.show', ['id' => 123]);

// Full URL with BASE_URL
url('/users');

// Redirect to route
redirect('user.show', ['id' => 123]);

// CSRF token
csrf_field();

// Old input (form repopulation)
old('email');

// And more...
```

---

## ✅ Best Practices

1. **Always validate** - Use constraints for security
2. **Use type hints** - Cleaner controller code
3. **Name important routes** - Easier refactoring
4. **Combine enhancements** - Maximum benefit

```php
// ✅ GOOD: All enhancements
$router->addParamRoute(
    '/users/{id:int}',
    Controller::class,
    'show',
    [],
    ['id' => '\d+']
)->name('user.show');

// ❌ BAD: No enhancements
$router->addParamRoute('/users/{id}', Controller::class, 'show');
```

---

**Ready to use Router v2.0!** 🚀
