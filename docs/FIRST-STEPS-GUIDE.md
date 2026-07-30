# 🚀 upMVC NoFramework - First Steps Guide

## ⚡ **Quick Start (5 Minutes)**

Get upMVC running in 5 minutes with this step-by-step guide.

---

## 📋 **Prerequisites Checklist**

Before starting, ensure you have:
- ✅ **PHP 8.1+** installed (`php --version`)
- ✅ **Composer** installed (`composer --version`)
- ✅ **Web server** or ability to run PHP built-in server
- ✅ **Git** for cloning (optional, can download ZIP)

---

## 🎯 **Step 1: Get upMVC (Choose One Method)**

### **Method A: Clone Production Repository (Recommended)**
```bash
git clone https://github.com/BitsHost/upMVC.git my-app
cd my-app
```

### **Method B: Download ZIP**
1. Go to: `https://github.com/BitsHost/upMVC`
2. Click "Code" → "Download ZIP"
3. Extract to your desired directory
4. Open terminal in that directory

### **Method C: Use Development Version (For Learning)**
```bash
git clone https://github.com/BitsHost/upMVC-DEV.git my-dev-app
cd my-dev-app
```

---

## 🔧 **Step 2: Install Dependencies**

```bash
# Install required packages
composer install

# Verify installation
composer validate
```

**Expected Output:**
```
Loading composer repositories with package information
Installing dependencies (including require-dev) from lock file
...
Generating autoload files
```

---

## ⚙️ **Step 3: Basic Configuration**

### **Create Environment File:**
```bash
# Copy the example — the destination is src/Etc/.env, not the project root.
# That is the only location Environment reads.
cp src/Etc/.env.example src/Etc/.env
```

Then set the two values that matter for a local run:

```env
SITE_PATH=            # empty when public/ is the document root
DOMAIN_NAME=http://localhost
```

### **Verify Configuration:**
Check that `src/Etc/Config.php` has reasonable defaults:
```php
public const SITE_PATH = '/';  // Root path
public const DOMAIN_NAME = 'http://localhost';
```

---

## 🌐 **Step 4: Start Development Server**

### **Option A: PHP Built-in Server (Easiest)**
```bash
# Start server on port 8000 — note the -t public
php -S localhost:8000 -t public

# Or with specific configuration
APP_ENV=development php -S localhost:8000 -t public
```

`-t public` is required. The only entry point is `public/index.php`; there is
no `index.php` at the project root, so serving the root directory returns 404
for every URL including `/`.

### **Option B: Apache / Nginx**
Point the virtual host's document root at the project's `public/` directory —
never at the project root, which would expose `src/Etc/.env`.

---

## ✅ **Step 5: Verify Installation**

### **Open Browser:**
Visit: `http://localhost:8000`

### **Expected Results:**
- ✅ **Success:** You see a working page (even if it's a simple page or 404)
- ✅ **No PHP errors** displayed
- ✅ **Page loads** without server errors

### **Common Issues:**
- **"Composer not found"** → Install Composer first
- **"Class not found"** → Run `composer install`
- **"Permission denied"** → Check file permissions
- **"500 Internal Error"** → Check PHP error logs

---

## 🏗️ **Step 6: Create Your First Module**

### **Using the agent (recommended):**
```bash
php src/Tools/upmvc-next.php --scaffold --goal "Create a Hello basic module"
# Paste docs/agent/generated/last-prompt.md into Cursor — agent scaffolds under src/Modules/
```

### **Manual Creation:**

> **Three things are not optional.** The kernel finds modules by scanning the
> filesystem, so it can only find yours if all three match exactly:
>
> 1. the folder is `Routes/` — **capital R** (lowercase fails on Linux)
> 2. the file is `Routes.php` — not `HelloRoutes.php`
> 3. the namespace is `App\Modules\Hello\…` — mirroring the folder path
>
> Get one wrong and the module is skipped **silently**: no error, no log line,
> just a 404 on your new route. Everything below already follows the rules.

#### **1. Create Directory:**
```bash
mkdir -p src/Modules/Hello/Routes
```

#### **2. Create Controller (`src/Modules/Hello/Controller.php`):**
```php
<?php
namespace App\Modules\Hello;

class Controller
{
    public function display()
    {
        echo "<h1>Hello from upMVC!</h1>";
        echo "<p>Your first module is working!</p>";
        echo "<p>Time: " . date('Y-m-d H:i:s') . "</p>";
    }
}
```

#### **3. Create Routes (`src/Modules/Hello/Routes/Routes.php`):**
```php
<?php
namespace App\Modules\Hello\Routes;

use App\Modules\Hello\Controller;

class Routes
{
    public function routes($router)
    {
        $router->addRoute('/hello', Controller::class, 'display');
    }
}
```

The method must be called `routes()` (or `Routes()`) and must **not** be
static — the kernel instantiates the class and calls the method on the object.

#### **4. Autoloading — nothing to do:**
`composer.json` already maps `App\` to `src/`, so `App\Modules\Hello\Controller`
resolves to `src/Modules/Hello/Controller.php` automatically. **Do not add
per-module entries to `composer.json`** — they are unnecessary, and a module
that needs them is a module whose namespace does not match its folder.

#### **5. Regenerate the classmap (optimised autoloader only):**
```bash
composer dump-autoload
```
Only needed if you installed with `--optimize-autoloader` or `--classmap-authoritative`.

---

## 🎉 **Step 7: Test Your Module**

### **Visit Your New Route:**
Open browser: `http://localhost:8000/hello`

### **Expected Result:**
```
Hello from upMVC!
Your first module is working!
Time: 2025-10-13 15:30:45
```

### **If You Get a 404:**

A 404 here almost always means the module was **not discovered**. Discovery is
a filesystem scan, so it fails quietly — there is nothing in the logs, because
from the kernel's point of view your module simply does not exist.

Check these four, in order. Any one of them produces exactly this 404:

| Check | Must be |
|---|---|
| Folder name | `Routes/` with a **capital R** |
| File name | `Routes.php` — not `HelloRoutes.php` |
| Namespace | `App\Modules\Hello\Routes` — mirrors the folder path |
| Method | `public function routes($router)` — **not** `static`, not `addRoutes` |

Confirm the kernel can see the file at all:

```bash
php -r 'foreach (glob("src/Modules/*/Routes/Routes.php") as $f) echo $f, PHP_EOL;'
```

If your module is not in that list, the problem is the folder or file name. If
it **is** listed but the route still 404s, the problem is the namespace or the
method name.

Only after those: re-run `composer dump-autoload` (needed only with an
optimised autoloader), and confirm you are visiting `/hello`, not `/hello/`.

---

## 📊 **Step 8: Explore the NoFramework**

### **Understanding the Structure (v2.0):**
```
your-app/
├── public/
│   └── index.php       # ← HTTP entry point (document root)
├── src/
│   ├── Etc/           # ← Core noFramework files
│   │   ├── Start.php  # ← Application bootstrap
│   │   ├── Router.php # ← URL routing
│   │   └── Config.php # ← Configuration
│   ├── Modules/       # ← Your application modules
│   │   └── Hello/     # ← Your first module!
│   └── Common/        # ← Shared base classes (controllers/views/models)
├── composer.json       # ← Dependencies & autoloading
└── vendor/             # ← Composer dependencies
```

### **Key Files to Know (v2.0):**
- **`public/index.php`** - HTTP entry point
- **`src/Etc/Start.php`** - NoFramework initialization  
- **`src/Etc/Router.php`** - URL routing system
- **`src/Modules/*/Controller.php`** - Handle requests
- **`src/Modules/*/Routes/Routes.php`** - Define URLs (capital `R`, exact filename)

---

## 🔄 **Next Steps (Choose Your Path)**

### **🎓 Learning Path:**
1. **Read the How-To Guide** - Comprehensive development guide
2. **Explore existing modules** - Look at demo modules for examples
3. **Read the FAQ** - Common questions and solutions
4. **Practice with database** - Add Model and database integration

### **🚀 Development Path:**
1. **Set up database** - Configure database connection
2. **Create CRUD module** - Build Create, Read, Update, Delete functionality
3. **Add authentication** - Implement user login system
4. **Style your app** - Add CSS and JavaScript

### **🏢 Production Path:**
1. **Configure production settings** - Set `APP_DEBUG=false`
2. **Set up proper web server** - Apache or Nginx configuration
3. **Configure database** - Production database setup
4. **Deploy your application** - Upload to your hosting provider

---

## 🛟 **Quick Troubleshooting**

### **🚨 Common Issues & Instant Fixes:**

#### **"Class 'App\Etc\Start' not found"**
```bash
composer install
```

#### **"404 Not Found" for all pages**
Check web server configuration:
```bash
# For Apache, ensure .htaccess exists:
echo "RewriteEngine On" > .htaccess
echo "RewriteCond %{REQUEST_FILENAME} !-f" >> .htaccess
echo "RewriteCond %{REQUEST_FILENAME} !-d" >> .htaccess
echo "RewriteRule ^(.*)$ index.php [QSA,L]" >> .htaccess
```

#### **"Permission denied" errors**
```bash
chmod -R 755 storage/
chmod -R 755 logs/
```

#### **Module not loading**
```bash
composer dump-autoload
```

#### **Database connection issues**
Check `src/Etc/ConfigDatabase.php` credentials and ensure database exists.

---

## 📚 **Learning Resources**

### **Next Documents to Read:**
1. **📘 HOW-TO-GUIDE.md** - Detailed development guide
2. **❓ FAQ.md** - Common questions and solutions
3. **📊 REPOSITORY-STRUCTURE-GUIDE.md** - Understanding different repositories

### **Example Code Locations (v2.0 layout):**
- **Demo modules:** `src/Modules/Test/`, `src/Modules/Enhanced/`
- **Configuration examples:** `src/Etc/Config.php`, `src/Etc/ConfigDatabase.php`
- **Advanced features:** `src/Modules/Enhanced/Controller.php`

---

## 💡 **Pro Tips for New Developers**

### **1. Start Simple:**
- Create one module at a time
- Test each feature before adding complexity
- Use the built-in PHP server for development

### **2. Follow Conventions:**
- Use PSR-4 autoloading standards
- Keep modules focused on single responsibility
- Use proper namespace naming

### **3. Debug Effectively:**
- Enable debug mode during development
- Check error logs regularly
- Use `var_dump()` and `error_log()` for debugging

### **4. Leverage the NoFramework:**
- Use the built-in caching system
- Implement middleware for common functionality
- Use the container for dependency injection

---

## 🎯 **Your 30-Day Learning Plan**

### **Week 1: Basics**
- ✅ Get upMVC running (you're here!)
- ✅ Create your first module
- ✅ Understand routing and controllers
- ✅ Add simple forms and data processing

### **Week 2: Intermediate**
- ✅ Add database integration
- ✅ Create CRUD operations
- ✅ Implement basic authentication
- ✅ Style your application

### **Week 3: Advanced**
- ✅ Use middleware for security
- ✅ Implement caching
- ✅ Add file uploads
- ✅ Create reusable components

### **Week 4: Production**
- ✅ Configure for production
- ✅ Set up proper web server
- ✅ Implement error handling
- ✅ Deploy your application

---

## 🎉 **Congratulations!**

You've successfully set up upMVC and created your first module! You're now ready to build amazing web applications with this modern PHP noFramework.

**Remember:** The upMVC community is here to help. Check the FAQ for common questions, and don't hesitate to explore the example modules for inspiration.

**Happy coding!** 🚀

---

*Continue your journey with the How-To Guide for detailed development instructions.*