# 📘 upMVC Framework - How-To Guide

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
cp etc/.env.example etc/.env

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
echo "APP_ENV=development" > etc/.env
```

---

## ⚙️ **Configuration**

### **Environment Configuration (`etc/.env`):**
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

### **Database Setup (`etc/ConfigDatabase.php`):**
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

### **Using Module Generator (Enhanced):**
```bash
# Generate a new module with full structure
php tools/modulegenerator-enhanced/generate.php create blog

# This creates:
# modules/blog/
# ├── Controller.php
# ├── Model.php  
# ├── View.php
# └── routes/Routes.php
```

### **Manual Module Creation:**

#### **1. Create Module Structure:**
```
modules/mymodule/
├── Controller.php
├── Model.php
├── View.php
└── routes/Routes.php
```

#### **2. Controller (`modules/mymodule/Controller.php`):**
```php
<?php
namespace Mymodule;

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

#### **3. Model (`modules/mymodule/Model.php`):**
```php
<?php
namespace Mymodule;

use upMVC\Database;

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

#### **4. View (`modules/mymodule/View.php`):**
```php
<?php
namespace Mymodule;

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

#### **5. Routes (`modules/mymodule/routes/Routes.php`):**
```php
<?php
namespace Mymodule\Routes;

class Routes
{
    public static function addRoutes($router): void
    {
        $router->addRoute('/mymodule', \Mymodule\Controller::class, 'display');
        $router->addRoute('/mymodule/create', \Mymodule\Controller::class, 'create');
        $router->addRoute('/mymodule/view/{id}', \Mymodule\Controller::class, 'view');
    }
}
```

### **6. Update Composer Autoloading:**
Add to `composer.json`:
```json
{
    "autoload": {
        "psr-4": {
            "Mymodule\\": "modules/mymodule/",
            "Mymodule\\Routes\\": "modules/mymodule/routes/"
        }
    }
}
```

Then run:
```bash
composer dump-autoload
```

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
namespace Mymodule;

use upMVC\Container\Container;

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
```php
// In your module's Routes.php
public static function addRoutes($router): void
{
    // Add middleware to specific routes
    $router->addRoute('/admin/mymodule', \Mymodule\Controller::class, 'admin')
           ->middleware(['auth', 'csrf']);
    
    // Group routes with middleware
    $router->group(['middleware' => ['auth']], function($router) {
        $router->addRoute('/mymodule/edit/{id}', \Mymodule\Controller::class, 'edit');
        $router->addRoute('/mymodule/delete/{id}', \Mymodule\Controller::class, 'delete');
    });
}
```

### **Using Events:**
```php
<?php
namespace Mymodule;

use upMVC\Events\EventDispatcher;

class Controller
{
    private EventDispatcher $events;
    
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
# In etc/.env
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