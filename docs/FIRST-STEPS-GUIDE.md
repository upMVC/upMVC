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
# Copy example environment file (if exists)
cp etc/.env.example etc/.env

# Or create basic .env file
echo "APP_ENV=development" > etc/.env
echo "APP_DEBUG=true" >> etc/.env
```

### **Verify Configuration:**
Check that `etc/Config.php` has reasonable defaults:
```php
public const SITE_PATH = '/';  // Root path
public const DOMAIN_NAME = 'http://localhost';
```

---

## 🌐 **Step 4: Start Development Server**

### **Option A: PHP Built-in Server (Easiest)**
```bash
# Start server on port 8000
php -S localhost:8000

# Or with specific configuration
APP_ENV=development php -S localhost:8000
```

### **Option B: Using Specific Directory**
```bash
# If upMVC is in subdirectory
php -S localhost:8000 -t . index.php
```

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

### **Using Module Generator (if available):**
```bash
# Generate a complete module
php tools/modulegenerator-enhanced/generate.php create hello

# This creates modules/hello/ with all necessary files
```

### **Manual Creation:**

#### **1. Create Directory:**
```bash
mkdir -p modules/hello/routes
```

#### **2. Create Controller (`modules/hello/Controller.php`):**
```php
<?php
namespace Hello;

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

#### **3. Create Routes (`modules/hello/routes/Routes.php`):**
```php
<?php
namespace Hello\Routes;

class Routes
{
    public static function addRoutes($router): void
    {
        $router->addRoute('/hello', \Hello\Controller::class, 'display');
    }
}
```

#### **4. Update Composer Autoloading:**
Add to `composer.json` in the `autoload` section:
```json
{
    "autoload": {
        "psr-4": {
            "Hello\\": "modules/hello/",
            "Hello\\Routes\\": "modules/hello/routes/"
        }
    }
}
```

#### **5. Regenerate Autoloader:**
```bash
composer dump-autoload
```

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

### **If It Doesn't Work:**
1. **Check URL:** Ensure you're visiting `/hello` not `/hello/`
2. **Check logs:** Look for error messages
3. **Verify autoloader:** Run `composer dump-autoload` again
4. **Check case sensitivity:** Ensure proper capitalization

---

## 📊 **Step 8: Explore the NoFramework**

### **Understanding the Structure:**
```
your-app/
├── index.php           # ← Entry point
├── composer.json       # ← Dependencies & autoloading
├── etc/               # ← Core noFramework files
│   ├── Start.php      # ← Application bootstrap
│   ├── Router.php     # ← URL routing
│   └── Config.php     # ← Configuration
├── modules/           # ← Your application modules
│   └── hello/         # ← Your first module!
└── vendor/            # ← Composer dependencies
```

### **Key Files to Know:**
- **`index.php`** - Application entry point
- **`etc/Start.php`** - NoFramework initialization  
- **`etc/Router.php`** - URL routing system
- **`modules/*/Controller.php`** - Handle requests
- **`modules/*/routes/Routes.php`** - Define URLs

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

#### **"Class 'upMVC\Start' not found"**
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
Check `etc/ConfigDatabase.php` credentials and ensure database exists.

---

## 📚 **Learning Resources**

### **Next Documents to Read:**
1. **📘 HOW-TO-GUIDE.md** - Detailed development guide
2. **❓ FAQ.md** - Common questions and solutions
3. **📊 REPOSITORY-STRUCTURE-GUIDE.md** - Understanding different repositories

### **Example Code Locations:**
- **Demo modules:** `modules/test/`, `modules/enhanced/`
- **Configuration examples:** `etc/Config.php`, `etc/ConfigDatabase.php`
- **Advanced features:** `modules/enhanced/Controller.php`

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