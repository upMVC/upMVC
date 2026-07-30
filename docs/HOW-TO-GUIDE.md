# 📘 upMVC NoFramework - How-To Guide

## 🚀 **Getting Started with upMVC**

### **System Requirements**
- PHP 8.1 or higher
- Composer (dependency management)
- Web server (Apache, Nginx, or built-in PHP server)
- Git (for cloning repository)

---

## 📥 **Installation Guide**

### **Method 1: Production Installation (Recommended)**

```bash
# Clone the clean production repository
git clone https://github.com/BitsHost/upMVC.git my-project
cd my-project

# Install dependencies
composer install

# Set up environment (copy and modify as needed)
cp src/Etc/.env.example .env

# Set permissions (Linux/Mac)
chmod -R 755 storage/
chmod -R 755 logs/
```

### **Method 2: Development Installation**

```bash
# Clone development repository for experimentation
git clone https://github.com/BitsHost/upMVC-DEV.git my-dev-project
cd my-dev-project

# Install dependencies including dev tools
composer install --dev

# Enable development mode
echo "APP_ENV=development" > .env
```

---

## ⚙️ **Configuration**

### **Environment Configuration (`.env` (project root)):**
```env
# Application Settings
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost
APP_TIMEZONE=UTC

# Database Configuration
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=upmvc_db
DB_USERNAME=root
DB_PASSWORD=

# Cache Configuration
CACHE_DRIVER=file
CACHE_PREFIX=upmvc_

# Security Settings
SESSION_LIFETIME=120
CSRF_PROTECTION=true
RATE_LIMIT=100
```

### **Database Setup (`src/Etc/ConfigDatabase.php`):**
```php
<?php
return [
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'upmvc'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ],
    ],
];
```

---

## 🏗️ **Creating Your First Module**

### **Using the agent scaffold pack (optional):**
```bash
php src/Tools/upmvc-next.php --scaffold --goal "Create a Blog CRUD module"
# Loads docs/agent/upmvc-scaffolds.json — paste last-prompt.md into your agent
```

### **Manual Module Creation:**

> **Discovery is a filesystem scan.** The kernel globs
> `src/Modules/*/Routes/Routes.php`, so the folder must be `Routes/` with a
> capital R, the file must be `Routes.php`, and the namespace must mirror the
> path as `App\Modules\Mymodule\…`. Miss any of them and the module is skipped
> **silently** — no error, no log line, just a 404 on every route it defines.

#### **1. Create Module Structure:**
```
src/Modules/Mymodule/
├── Controller.php
├── Model.php
├── View.php
└── Routes/Routes.php        ← capital R, file named exactly Routes.php
```

#### **2. Controller (`src/Modules/Mymodule/Controller.php`):**
```php
<?php
namespace App\Modules\Mymodule;

class Controller
{
    public function display()
    {
        $model = new Model();
        $data = $model->getData();
        
        $view = new View();
        $view->render('index', $data);
    }
    
    public function create()
    {
        // Handle POST requests
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $model = new Model();
            $model->create($_POST);
            header('Location: /mymodule');
            exit;
        }
        
        $view = new View();
        $view->render('create');
    }
}
```

#### **3. Model (`src/Modules/Mymodule/Model.php`):**
```php
<?php
namespace App\Modules\Mymodule;

use App\Etc\Database;

class Model
{
    private $db;
    
    public function __construct()
    {
        $this->db = new Database();
    }
    
    public function getData(): array
    {
        return $this->db->query("SELECT * FROM items")->fetchAll();
    }
    
    public function create(array $data): bool
    {
        $sql = "INSERT INTO items (name, description) VALUES (?, ?)";
        return $this->db->execute($sql, [$data['name'], $data['description']]);
    }
    
    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM items WHERE id = ?";
        return $this->db->query($sql, [$id])->fetch();
    }
}
```

#### **4. View (`src/Modules/Mymodule/View.php`):**
```php
<?php
namespace App\Modules\Mymodule;

class View
{
    public function render(string $template, array $data = []): void
    {
        extract($data);
        
        switch ($template) {
            case 'index':
                $this->renderIndex($data);
                break;
            case 'create':
                $this->renderCreate();
                break;
            default:
                http_response_code(404);
                echo "Template not found";
        }
    }
    
    private function renderIndex(array $items): void
    {
        echo "<h1>My Module</h1>";
        echo "<a href='/mymodule/create'>Add New Item</a>";
        echo "<ul>";
        foreach ($items as $item) {
            echo "<li>{$item['name']}: {$item['description']}</li>";
        }
        echo "</ul>";
    }
    
    private function renderCreate(): void
    {
        echo "<h1>Create New Item</h1>";
        echo "<form method='POST'>";
        echo "<input type='text' name='name' placeholder='Name' required>";
        echo "<textarea name='description' placeholder='Description'></textarea>";
        echo "<button type='submit'>Create</button>";
        echo "</form>";
    }
}
```

#### **5. Routes (`src/Modules/Mymodule/Routes/Routes.php`):**
```php
<?php
namespace App\Modules\Mymodule\Routes;

use App\Modules\Mymodule\Controller;

class Routes
{
    public function routes($router)
    {
        $router->addRoute('/mymodule', Controller::class, 'display');
        $router->addRoute('/mymodule/create', Controller::class, 'create');
        $router->addParamRoute('/mymodule/view/{id}', Controller::class, 'view');
    }
}
```

The method must be named `routes()` (or `Routes()`) and must **not** be static
— the kernel instantiates the class, then calls the method on the instance.
Parameterised paths like `{id}` go through `addParamRoute()`, not `addRoute()`.

### **6. Autoloading — nothing to do:**
`composer.json` already maps `App\` to `src/`, so `App\Modules\Mymodule\Controller`
resolves to `src/Modules/Mymodule/Controller.php` with no configuration.

**Do not add per-module PSR-4 entries.** They are unnecessary, and needing one
is a sign the namespace does not mirror the folder — which is the same mistake
that stops discovery from finding the module at all.

Re-run `composer dump-autoload` only if you installed with
`--optimize-autoloader` or `--classmap-authoritative`.

---

## 🌐 **Web Server Configuration**

### **Apache (.htaccess):**
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

### **Nginx:**
```nginx
server {
    listen 80;
    server_name localhost;
    root /path/to/upmvc;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### **Built-in PHP Server (Development):**
```bash
# Start development server
php -S localhost:8000 -t . index.php

# Or use with specific configuration
PHP_ENV=development php -S localhost:8000 -t . index.php
```

---

## 🔧 **Advanced Features**

### **Using Dependency Injection:**
```php
<?php
namespace App\Modules\Mymodule;

use App\Etc\Container\Container;

class Controller
{
    private Container $container;
    
    public function __construct(Container $container)
    {
        $this->container = $container;
    }
    
    public function display()
    {
        // Get service from container
        $cacheService = $this->container->make('cache');
        $data = $cacheService->remember('mymodule.data', 3600, function() {
            return (new Model())->getData();
        });
        
        (new View())->render('index', $data);
    }
}
```

### **Using Middleware:**

Middleware is the **fourth argument** to `addRoute()` — an array of registered
middleware names. There is no fluent `->middleware()` and no `group()`; the
router builds a plain array of routes, and `addRoute()` returns nothing to
chain from.

```php
// In your module's Routes/Routes.php
public function routes($router)
{
    // Attach middleware to a single route
    $router->addRoute('/admin/mymodule', Controller::class, 'admin', ['auth', 'csrf']);

    // Repeat the array to apply the same middleware to several routes
    $router->addParamRoute('/mymodule/edit/{id}', Controller::class, 'edit', ['auth']);
    $router->addParamRoute('/mymodule/delete/{id}', Controller::class, 'delete', ['auth']);
}
```

Names must already be registered. `csrf`, `rate_limit`, `cors`, `jwt` and
`auth` are wired up in `src/Etc/Start.php`. An unregistered name throws
`RuntimeException: Unknown route middleware '<name>'` on the first request that
hits the route — a typo fails loudly rather than quietly dropping the
protection you meant to add.

### **Using Events:**
```php
<?php
namespace App\Modules\Mymodule;

use App\Etc\Events\EventDispatcher;

class Controller
{
    private EventDispatcher $events;

    public function __construct(?EventDispatcher $events = null)
    {
        $this->events = $events ?? new EventDispatcher();
    }

    public function create()
    {
        $model = new Model();
        $item = $model->create($_POST);

        // Dispatch event after creation
        $this->events->dispatch('item.created', ['item' => $item]);

        header('Location: /mymodule');
    }
}
```

`dispatch()` takes a **string** event name plus a data array, or an `Event`
object. The kernel itself dispatches nothing — the dispatcher exists for your
modules to use, so `item.created` fires only if something calls `listen()` for
it.

---

## 🧪 **Testing**

### **Unit Testing Setup:**
```bash
# Install PHPUnit (if not included)
composer require --dev phpunit/phpunit

# Create test directory
mkdir tests
mkdir tests/Unit
mkdir tests/Feature
```

### **Sample Test:**
```php
<?php
// tests/Unit/MymoduleModelTest.php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Mymodule\Model;

class MymoduleModelTest extends TestCase
{
    public function testGetData()
    {
        $model = new Model();
        $data = $model->getData();
        
        $this->assertIsArray($data);
    }
}
```

---

## 📚 **Best Practices**

### **1. Module Organization:**
- Keep modules focused on single responsibility
- Use proper namespacing (PSR-4)
- Separate concerns (Controller, Model, View)
- Include routes in dedicated Routes class

### **2. Security:**
- Always validate and sanitize input
- Use CSRF protection for forms
- Implement proper authentication
- Use prepared statements for database queries

### **3. Performance:**
- Utilize caching for expensive operations
- Optimize database queries
- Use autoloading efficiently
- Enable production optimizations

### **4. Code Quality:**
```bash
# Run composer validation
composer validate

# Check autoload optimization
composer dump-autoload --optimize

# Clear caches in production
php artisan cache:clear  # If you implement artisan-like commands
```

---

## 🔍 **Debugging**

### **Enable Debug Mode:**
```env
# In .env (project root)
APP_ENV=development
APP_DEBUG=true
```

### **View Logs:**
```bash
# Check error logs
tail -f logs/errors.log

# Check application logs
tail -f logs/app.log
```

### **Debug Database Queries:**
```php
// Enable query logging in ConfigDatabase.php
'log_queries' => true,
'queries_log_file' => 'logs/queries.log'
```

---

*This guide covers the essential aspects of working with upMVC. For more advanced topics, check the FAQ and First Steps guides.*