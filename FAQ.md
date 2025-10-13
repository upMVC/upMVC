# ❓ upMVC Framework - FAQ

## 🔍 **Frequently Asked Questions**

---

## 📥 **Installation & Setup**

### **Q: Which repository should I use for my project?**
**A:** 
- **Production:** Use `d:\GitHub\upMVC\` - This is the clean, production-ready version
- **Development/Learning:** Use `upMVC-DEV` - Contains additional modules and development tools
- **Experimentation:** Use other dev repositories for testing new features

### **Q: Why do I get "composer not found" error?**
**A:** You need to install Composer first:
```bash
# Windows (using Chocolatey)
choco install composer

# Or download from https://getcomposer.org/download/
# Linux/Mac
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### **Q: What PHP version is required?**
**A:** upMVC requires **PHP 8.1 or higher**. Check your version:
```bash
php --version
```

### **Q: How do I fix "vendor/autoload.php not found" error?**
**A:** Run composer install in your project directory:
```bash
cd your-project-directory
composer install
```

---

## ⚙️ **Configuration Issues**

### **Q: How do I change the base URL/path?**
**A:** Edit `etc/Config.php`:
```php
public const SITE_PATH = '/your-app-path';  // e.g., '/myapp' or '/' for root
public const DOMAIN_NAME = 'https://yourdomain.com';
```

### **Q: Database connection fails, what should I check?**
**A:** 
1. **Check credentials** in `etc/ConfigDatabase.php`
2. **Verify database exists:**
   ```sql
   CREATE DATABASE your_database_name;
   ```
3. **Test connection:**
   ```bash
   mysql -u username -p -h localhost database_name
   ```
4. **Check PHP extensions:**
   ```bash
   php -m | grep pdo
   php -m | grep mysql
   ```

### **Q: How do I enable/disable debug mode?**
**A:** 
- **Enable:** Set `APP_DEBUG=true` in `etc/.env` or modify `etc/Config.php`
- **Disable:** Set `APP_DEBUG=false` for production

### **Q: Sessions not working, what's wrong?**
**A:** Check these common issues:
1. **Session directory permissions:**
   ```bash
   chmod 755 storage/sessions/
   ```
2. **PHP session configuration:**
   ```php
   // In etc/Config.php
   ini_set('session.save_path', __DIR__ . '/../storage/sessions');
   ```
3. **Headers already sent:** Check for output before `session_start()`

---

## 🏗️ **Module Development**

### **Q: My new module isn't loading, why?**
**A:** Check these steps:
1. **Namespace matches directory:** 
   - Namespace: `MyModule`
   - Directory: `modules/mymodule/`
2. **Added to composer.json:**
   ```json
   "MyModule\\": "modules/mymodule/"
   ```
3. **Regenerated autoloader:**
   ```bash
   composer dump-autoload
   ```
4. **Routes class exists:**
   ```php
   // modules/mymodule/routes/Routes.php
   namespace MyModule\Routes;
   class Routes {
       public static function addRoutes($router): void { ... }
   }
   ```

### **Q: How do I rename an existing module?**
**A:** Follow these steps:
1. **Rename directory:** `modules/oldname/` → `modules/newname/`
2. **Update namespace in all PHP files:** `namespace OldName;` → `namespace NewName;`
3. **Update composer.json:**
   ```json
   "NewName\\": "modules/newname/"
   ```
4. **Regenerate autoloader:** `composer dump-autoload`
5. **Update any hard-coded references**

### **Q: Can I delete demonstration modules?**
**A:** **Yes!** Modules in `/modules/` are optional demonstration code. Safe to remove:
- `modules/enhanced/` - Advanced features demo
- `modules/test/` - Testing examples
- `modules/react*/` - React integration examples
- Keep only modules you're actually using

### **Q: How do I create nested modules?**
**A:** upMVC supports nested modules:
```
modules/
├── parent/
│   ├── Controller.php          # namespace Parent;
│   └── modules/
│       └── child/
│           ├── Controller.php  # namespace Parent\Child;
│           └── routes/Routes.php
```

---

## 🌐 **Routing & URLs**

### **Q: My routes return 404 errors, what's wrong?**
**A:** Check these common issues:
1. **Web server configuration:**
   - **Apache:** Ensure `.htaccess` is working and `mod_rewrite` enabled
   - **Nginx:** Configure `try_files` directive properly
2. **Route registration:**
   ```php
   // In your module's Routes.php
   $router->addRoute('/mypath', \MyModule\Controller::class, 'method');
   ```
3. **Method exists in controller:**
   ```php
   public function method() { ... }
   ```

### **Q: How do I add parameters to routes?**
**A:** Use curly braces for parameters:
```php
$router->addRoute('/user/{id}', \User\Controller::class, 'show');
$router->addRoute('/post/{id}/comment/{comment_id}', \Blog\Controller::class, 'showComment');

// In controller:
public function show($id) {
    // $id contains the route parameter
}
```

### **Q: How do I handle different HTTP methods (POST, PUT, DELETE)?**
**A:** Check request method in your controller:
```php
public function handleUser() {
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            return $this->show();
        case 'POST':
            return $this->create();
        case 'PUT':
            return $this->update();
        case 'DELETE':
            return $this->delete();
    }
}
```

---

## 🔒 **Security**

### **Q: How do I implement CSRF protection?**
**A:** upMVC includes built-in CSRF protection:
```php
// In forms, add CSRF token:
echo '<input type="hidden" name="csrf_token" value="' . \upMVC\Security::generateCsrf() . '">';

// Validation is automatic for POST requests when enabled in config
```

### **Q: How do I add authentication to routes?**
**A:** Use the auth middleware:
```php
// In your Routes.php
$router->addRoute('/admin/dashboard', \Admin\Controller::class, 'dashboard')
       ->middleware(['auth']);

// Or check manually in controller:
public function dashboard() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit;
    }
    // ... protected content
}
```

### **Q: How do I prevent SQL injection?**
**A:** Always use prepared statements:
```php
// ✅ CORRECT - Using prepared statements
$sql = "SELECT * FROM users WHERE id = ? AND status = ?";
$result = $this->db->query($sql, [$id, $status]);

// ❌ WRONG - Direct string concatenation
$sql = "SELECT * FROM users WHERE id = " . $id;  // VULNERABLE!
```

---

## 🚀 **Performance**

### **Q: How do I enable caching?**
**A:** upMVC includes a caching system:
```php
use upMVC\Cache\CacheManager;

// Cache data for 1 hour (3600 seconds)
$data = CacheManager::remember('expensive_operation', 3600, function() {
    return performExpensiveOperation();
});

// Manual cache operations
CacheManager::put('key', $data, 3600);
$cached = CacheManager::get('key', $default);
```

### **Q: How do I optimize for production?**
**A:** Follow these steps:
1. **Optimize composer autoloader:**
   ```bash
   composer dump-autoload --optimize --no-dev
   ```
2. **Disable debug mode:**
   ```php
   // etc/Config.php
   'debug' => false,
   ```
3. **Enable PHP OPcache** in `php.ini`:
   ```ini
   opcache.enable=1
   opcache.memory_consumption=128
   opcache.max_accelerated_files=4000
   ```
4. **Use file-based caching** instead of database caching

---

## 🐛 **Debugging & Troubleshooting**

### **Q: How do I debug errors?**
**A:** 
1. **Enable debug mode:** `APP_DEBUG=true` in `.env`
2. **Check error logs:**
   ```bash
   tail -f logs/errors.log
   ```
3. **Add debug output:**
   ```php
   error_log("Debug: " . print_r($variable, true));
   var_dump($variable); // Only in development!
   ```

### **Q: "Class not found" errors, how to fix?**
**A:** 
1. **Check namespace matches directory structure**
2. **Verify composer.json autoload section**
3. **Regenerate autoloader:** `composer dump-autoload`
4. **Case sensitivity:** Ensure proper case in filenames and namespaces

### **Q: Database queries not working?**
**A:** 
1. **Enable query logging:**
   ```php
   // In ConfigDatabase.php
   'log_queries' => true,
   ```
2. **Check SQL syntax:**
   ```php
   try {
       $result = $this->db->query($sql, $params);
   } catch (PDOException $e) {
       error_log("SQL Error: " . $e->getMessage());
   }
   ```

---

## 📱 **Deployment**

### **Q: How do I deploy to production?**
**A:** 
1. **Use production repository:** `d:\GitHub\upMVC\`
2. **Configure environment:**
   ```env
   APP_ENV=production
   APP_DEBUG=false
   ```
3. **Set proper permissions:**
   ```bash
   chmod -R 755 storage/
   chmod -R 755 logs/
   chmod 644 etc/.env
   ```
4. **Configure web server** (Apache/Nginx)
5. **Install dependencies:** `composer install --no-dev --optimize-autoloader`

### **Q: File permissions issues on shared hosting?**
**A:** 
```bash
# Set proper permissions
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod 755 storage/ logs/
```

---

## 🔄 **Migration & Updates**

### **Q: How do I migrate from old upMVC version?**
**A:** 
1. **Backup your current project**
2. **Compare module structures** - update to new format if needed
3. **Update composer.json** - ensure PSR-4 compliance
4. **Test thoroughly** in development environment
5. **Update incrementally** rather than all at once

### **Q: How do I update upMVC framework?**
**A:** 
1. **Check for updates** in the main repository
2. **Review changelog** for breaking changes
3. **Test in development** environment first
4. **Update composer dependencies:** `composer update`

---

## 🆘 **Common Error Messages**

### **"Fatal error: Uncaught Error: Class 'upMVC\Start' not found"**
**Solution:** Run `composer install` to generate autoloader

### **"404 Not Found" for all routes**
**Solution:** Check web server configuration, ensure URL rewriting works

### **"Headers already sent" error**
**Solution:** Remove any output (spaces, BOM) before PHP opening tags

### **"Permission denied" errors**
**Solution:** Set proper file permissions (755 for directories, 644 for files)

### **"Cannot redeclare class" error**
**Solution:** Check for duplicate class definitions or include statements

---

## 📞 **Getting Help**

### **Still having issues?**
1. **Check the documentation** in other guide files
2. **Review example modules** in the development repository
3. **Enable debug mode** and check error logs
4. **Search for similar issues** in the codebase
5. **Create minimal reproduction** of the problem

### **Reporting Bugs:**
When reporting issues, include:
- PHP version (`php --version`)
- upMVC version/repository used
- Error messages (full stack trace)
- Steps to reproduce
- Expected vs actual behavior

---

*This FAQ covers the most common questions. For detailed implementation examples, see the How-To Guide and First Steps documentation.*