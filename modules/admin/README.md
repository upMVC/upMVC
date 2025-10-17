# Admin Dashboard Module

Complete admin panel with user CRUD operations for upMVC NoFramework.

## Features

✅ **Dashboard** - Overview with user statistics  
✅ **User Management** - Full CRUD operations  
✅ **Authentication Protected** - Requires login to access  
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
- Dynamic route generation from database
- Queries database for user IDs on each request
- Creates explicit routes for each user
- **For advanced routing strategies (caching, pattern matching)**, see:
  - 📚 **[docs/routing/README.md](../../docs/routing/README.md)** - Complete routing guide
  - 🚀 **[docs/routing/ROUTING_STRATEGIES.md](../../docs/routing/ROUTING_STRATEGIES.md)** - Performance comparison and migration guides
  - 💾 **[docs/routing/examples/Routes_WithCache.php](../../docs/routing/examples/Routes_WithCache.php)** - Cache implementation example

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
