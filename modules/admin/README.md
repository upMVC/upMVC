# Admin Dashboard Module

Complete admin panel with user CRUD operations for upMVC NoFramework.

> **📌 Note:** This module is a **reference implementation**. You can delete it if you don't need admin functionality. It demonstrates: route caching, CRUD operations, controller-level authentication, and flash messages. See [Module Philosophy](../../docs/MODULE_PHILOSOPHY.md) for more about upMVC's modular approach.

## Features

✅ **Dashboard** - Overview with user statistics  
✅ **User Management** - Full CRUD operations  
✅ **Authentication Protected** - Requires login to access (controller-level check)
✅ **Cached Routes** - Database routes cached for performance  
✅ **Flash Messages** - Success/error notifications  
✅ **Clean UI** - Responsive design with inline styles  
✅ **Pure PHP** - No JavaScript frameworks, following upMVC philosophy  

## Routes

```
/admin                      - Dashboard (stats and quick actions)
/admin/users                - List all users
/admin/users/add            - Add new user form
/admin/users/edit/{id}      - Edit user form
/admin/users/delete/{id}    - Delete user (with confirmation)
```

## 🎓 Routing Strategy (Educational Purpose)

This module demonstrates **THREE routing strategies** for educational comparison:

### 1. Current Implementation: Router V2 Enhanced ⭐⭐⭐ (Routes.php, Controller.php)

**Latest & Recommended:** Uses Router v2.0 with full type safety and validation

**Features:**
- ✅ Type hints: `{id:int}` auto-casts to integer
- ✅ Validation: `\d+` regex ensures only numeric IDs
- ✅ Named routes: `->name('admin.user.edit')` for URL generation
- ✅ Security: Invalid IDs rejected at router level
- ✅ Clean code: No manual casting or validation in controller

```php
// routes/Routes.php (Router V2 Enhanced)
$router->addParamRoute(
    '/admin/users/edit/{id:int}',
    Controller::class,
    'display',
    [],
    ['id' => '\d+']
)->name('admin.user.edit');

// Controller.php - Notice: No casting needed!
$userId = $_GET['id'];  // Already int from Router V2
```

**Performance:** O(1) memory, 0.5ms matching, scales to millions  
**Best for:** All projects (small and large)  
**Documentation:** [docs/routing/ROUTER_V2_EXAMPLES.md](../../docs/routing/ROUTER_V2_EXAMPLES.md)

---

### 2. Backup: Basic Parameterized Routes (Routesd.php, Controllerd.php)

**Simple param routing:** Basic `{id}` placeholder without type hints

**Features:**
- ✅ Pattern matching: `/admin/users/edit/{id}`
- ❌ No type casting: Manual `(int)$_GET['id']` needed
- ❌ No validation: Manual `ctype_digit()` check required
- ✅ Scalable: O(1) memory, grows with route count only

```php
// routes/Routesd.php (Basic Param)
$router->addParamRoute('/admin/users/edit/{id}', Controller::class, 'display');

// Controllerd.php - Manual validation required
$id = $_GET['id'] ?? null;
if (!ctype_digit((string)$id)) abort(400);
$userId = (int)$id;
```

**Performance:** O(1) memory, 1ms matching  
**Best for:** Learning param routing basics  
**Documentation:** [docs/routing/PARAMETERIZED_ROUTING.md](../../docs/routing/PARAMETERIZED_ROUTING.md)

---

### 3. Backup: Cached Expansion (Routesc.php, Controllerc.php)

**Database-driven routes:** Pre-generates route for each user

**Features:**
- ✅ Security-first: Only valid user IDs get routes
- ✅ Fast matching: Exact routes (no pattern matching)
- ❌ Memory overhead: O(N) grows with user count
- ❌ Cache invalidation: Must clear on create/delete

```php
// routes/Routesc.php (Cached Expansion)
foreach ($users as $user) {
    $router->addRoute('/admin/users/edit/' . $user['id'], Controller::class, 'display');
}

// Controllerc.php - Regex route matching
case (preg_match('/^\/admin\/users\/edit\/(\d+)$/', $reqRoute, $matches) ? true : false):
    $userId = (int)$matches[1];
```

**Performance:** O(N) memory, 2ms matching with cache  
**Best for:** Small projects (<1,000 users), security-critical apps  
**Documentation:** [docs/routing/README.md](../../docs/routing/README.md)

---

### 📊 Quick Comparison

| Feature | Router V2 ⭐⭐⭐ | Basic Param | Cached |
|---------|---------------|-------------|--------|
| Type Casting | ✅ Auto | ❌ Manual | ❌ Manual |
| Validation | ✅ Router | ❌ Controller | ✅ Router |
| Named Routes | ✅ Yes | ❌ No | ❌ No |
| Memory | O(1) | O(1) | O(N) |
| Speed | 0.5ms | 1ms | 2ms |
| Cache | ❌ None | ❌ None | ✅ File |
| Best for | All projects | Learning | Small apps |

### 🎯 Which Should You Use?

**Choose Router V2 (current)** if:
- ✅ You want the cleanest, most maintainable code
- ✅ You need type safety and validation
- ✅ You plan to scale beyond 1,000+ users
- ✅ You want URL generation with `Helpers::route()`

**Choose Basic Param (Routesd.php)** if:
- 📚 You're learning how parameterized routing works
- 📚 You want to understand the basics before Router V2

**Choose Cached Expansion (Routesc.php)** if:
- 🔒 You prioritize security (only valid IDs get routes)
- 📦 Small project (<1,000 users)
- 🎓 You're learning different routing strategies

**Learn more:**
- 🚀 **[docs/routing/ROUTER_V2_EXAMPLES.md](../../docs/routing/ROUTER_V2_EXAMPLES.md)** - Router V2 complete guide
- 📚 **[docs/routing/PARAMETERIZED_ROUTING.md](../../docs/routing/PARAMETERIZED_ROUTING.md)** - Basic param routing guide
- 🎯 **[docs/routing/README.md](../../docs/routing/README.md)** - Routing overview and decision tree

## Installation

### 1. Already configured! ✅

The admin module is:
- ✅ Already in `/modules/admin/`
- ✅ Routes registered in `/etc/InitMods.php`
- ✅ Namespace in `composer.json`
- ✅ Autoloader refreshed

### 2. Database Table

Ensure you have a `user` table with these columns:

```sql
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullname` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## Usage

### Access the Dashboard

1. **Login first**: Visit `/auth` and login
2. **Navigate to admin**: Visit `/admin`
3. **Manage users**: Click "Manage Users" button

### Create a User

1. Go to `/admin/users/add`
2. Fill in the form (username, email, password, full name)
3. Click "Create User"

### Edit a User

1. Go to `/admin/users`
2. Click "Edit" button on any user
3. Update fields (leave password blank to keep current)
4. Click "Update User"

### Delete a User

1. Go to `/admin/users`
2. Click "Delete" button on any user
3. Confirm deletion

## Code Structure

### Model (`Model.php`)
- Extends `BaseModel` for CRUD operations
- Methods: `getAllUsers()`, `getUserById()`, `createUser()`, `updateUser()`, `deleteUser()`, `getUserCount()`
- Handles password hashing automatically

### Controller (`Controller.php`)
- Authentication check on all routes
- Route handling with regex for dynamic IDs
- POST/GET method separation
- Flash messages for user feedback
- Redirects after actions

### View (`View.php`)
- Uses `BaseView` for consistent layout
- Four main views: dashboard, users_list, user_form, error
- Inline styles (no external CSS)
- Flash message rendering
- XSS protection with `htmlspecialchars()`

### Routes (`routes/Routes.php`)
- **Current (Router V2 Enhanced):** Type-safe parameterized routing
  - Registers patterns with type hints: `/admin/users/edit/{id:int}`
  - Regex validation: `['id' => '\d+']` for security
  - Named routes: `->name('admin.user.edit')` for URL generation
  - Auto-casts params to int/float/bool
  - Scalable to millions of users
  
- **Backup (Routesd.php):** Basic parameterized routing
  - Simple placeholders: `/admin/users/edit/{id}`
  - No type hints or validation
  - Manual casting required in controller
  
- **Backup (Routesc.php):** Cached expansion implementation  
  - Pre-generates routes for each user from database
  - Caches to `etc/storage/cache/admin_routes.php`
  - Security-first: only valid user IDs get routes
  - Ideal for small projects (< 1,000 users)

**Learn more:**
  - 🚀 **[docs/routing/ROUTER_V2_EXAMPLES.md](../../docs/routing/ROUTER_V2_EXAMPLES.md)** - Router V2 complete guide with examples
  - 📚 **[docs/routing/PARAMETERIZED_ROUTING.md](../../docs/routing/PARAMETERIZED_ROUTING.md)** - Basic param routing guide
  - 🎯 **[docs/routing/README.md](../../docs/routing/README.md)** - Routing overview and decision tree

## Customization

### Change Table Name

Edit `Model.php`:
```php
private string $table = 'your_table_name';
```

### Add More Fields

1. Update database table
2. Update `createUser()` and `updateUser()` in Controller
3. Update form in View's `renderUserForm()`

### Customize Styles

All styles are inline in `View.php`. Modify the `style=""` attributes to match your design.

### Add More Stats to Dashboard

Edit `Model.php`:
```php
public function getYourStat(): int {
    // Your SQL query here
}
```

Then update `Controller->dashboard()` and `View->renderDashboard()`.

## Security Features

- ✅ **Authentication required** - All routes protected
- ✅ **Password hashing** - Using `password_hash()` with `PASSWORD_DEFAULT`
- ✅ **XSS protection** - All output uses `htmlspecialchars()`
- ✅ **SQL injection protection** - BaseModel uses prepared statements
- ✅ **Delete confirmation** - JavaScript confirm dialog

## upMVC Philosophy

This module follows upMVC's **NoFramework** principles:

- **Pure PHP** - Direct `$_POST`, `$_SESSION` access
- **No dependencies** - Only extends BaseModel/BaseView
- **Simple routing** - Direct controller methods
- **No magic** - Clear, readable code
- **Freedom** - Easy to modify and extend

## Testing

1. Ensure you're logged in: `/auth`
2. Visit `/admin` - Should see dashboard
3. Click "Manage Users" - Should list users
4. Add a test user - Should create and redirect
5. Edit the user - Should update
6. Delete the user - Should confirm and delete

## Troubleshooting

**"Headers already sent" error:**
- Check for output before `header()` calls
- Ensure no BOM in PHP files

**Users not showing:**
- Verify `user` table exists
- Check database connection in `/etc/ConfigDatabase.php`

**Routes not working:**
- Run `composer dump-autoload`
- Check `/etc/InitMods.php` includes AdminRoutes

**Can't access /admin:**
- Login first at `/auth`
- Check `$_SESSION['logged']` is set to `true`

## Credits

Built with ❤️ following upMVC's pure PHP approach.  
No frameworks. No bloat. Just PHP.
