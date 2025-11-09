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

This module demonstrates **TWO routing strategies** for educational comparison:

### Current Implementation: Parameterized Routes ⭐ (Routes.php)

**Used for:** Scalable applications with many users (1,000+)

Routes are registered as patterns (e.g., `/admin/users/edit/{id}`):
- No database query during route registration
- Router injects `$_GET['id']` at dispatch time
- Controller validates ID and checks existence
- Memory usage: O(1) - constant regardless of user count
- Perfect for large datasets

```php
// routes/Routes.php
$router->addParamRoute('/admin/users/edit/{id}', Controller::class, 'display');
$router->addParamRoute('/admin/users/delete/{id}', Controller::class, 'display');
```

### Backup Implementation: Cached Expansion (Routesc.php, Controllerc.php)

**Preserved for:** Small projects, learning, security-first approach

Routes are expanded and cached for each user:
- Database query on first request or after cache expires
- Pre-validates user IDs (invalid IDs get 404 at router level)
- Cache file: `etc/storage/cache/admin_routes.php`
- Memory usage: O(N) - grows with user count
- Excellent for small admin panels (< 1,000 users)

```php
// routes/Routesc.php (backup)
foreach ($users as $user) {
    $router->addRoute('/admin/users/edit/' . $user['id'], Controller::class, 'display');
}
```

**Why both?**
- Learn different routing patterns
- Choose based on your project scale
- Copy Routesc.php/Controllerc.php for small projects
- Use current implementation for large applications

**Migration:** See [docs/routing/PARAMETERIZED_ROUTING.md](../../docs/routing/PARAMETERIZED_ROUTING.md) for complete guide.

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
- **Current:** Parameterized routing implementation
  - Registers patterns: `/admin/users/edit/{id}`, `/admin/users/delete/{id}`
  - No database query required during route registration
  - Scalable to millions of users
  
- **Backup (Routesc.php):** Cached expansion implementation  
  - Pre-generates routes for each user from database
  - Caches to `etc/storage/cache/admin_routes.php`
  - Security-first: only valid user IDs get routes
  - Ideal for small projects (< 1,000 users)

**Learn more:**
  - 📚 **[docs/routing/PARAMETERIZED_ROUTING.md](../../docs/routing/PARAMETERIZED_ROUTING.md)** - Complete parameterized routing guide
  - 🚀 **[docs/routing/ROUTING_STRATEGIES.md](../../docs/routing/ROUTING_STRATEGIES.md)** - Performance comparison and migration guides

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
