# 📚 upMVC noFramework v1.0.3 - Complete Documentation

> **Modern, lightweight PHP noframework for rapid development with clean architecture**

**Status: ✅ Production Ready** | **PHP 8.1+** | **PSR-4 Compliant** | **MIT License**

## 🎨 **NEW: Modern UI System**

upMVC v1.0.3 introduces a **contemporary design system** while maintaining complete backward compatibility:

- **🌟 Modern BaseView**: Contemporary CSS Grid/Flexbox layouts with dark mode
- **📱 Responsive Design**: Mobile-first approach with modern navigation
- **⚡ Alpine.js Integration**: Lightweight interactivity (~40KB vs 87KB jQuery)
- **🎯 Zero Breaking Changes**: Drop-in replacement for existing BaseView

**Quick Demo:** `/test` (original) vs `/test/modern` (new design)
**Documentation:** [Modern BaseView Guide](MODERN_BASEVIEW_GUIDE.md) | [Demo Instructions](MODERN_DEMO.md)

## 🌟 **What is upMVC?**

A lightweight, modern PHP 8.1+ noFramework built on Modular MVC (MMVC) architecture. Designed for developers who want flexibility without noFramework bloat. Features true modularity, "PHP Islands" for frontend integration (React/Vue), dependency injection, middleware pipeline, and clean routing. No forced conventions - use pure PHP or integrate modern JS noFrameworks as needed. Perfect for rapid development while maintaining performance and flexibility.

✨ **Key Features:** Modular architecture • PHP 8.1+ • PSR-4 compliant • Dependency injection • Middleware support • Frontend noFramework integration • Minimal learning curve

### 🚀 **Deploy Any JavaScript Framework**

upMVC excels at integrating **pre-built JavaScript applications** from any framework:

- **⚛️ React** - `npm run build` → deploy to module/public ([Guide](docs/REACT_BUILD_INTEGRATION.md))
- **💚 Vue** - Production builds with Vite/Webpack ([Guide](docs/VUE_BUILD_INTEGRATION.md))
- **🔥 Svelte** - SvelteKit or standalone builds  
- **⚡ Any Framework** - Angular, Solid, Qwik, etc.

**Your PHP module serves the built app** - no complex webpack configs, no dev servers in production. Just build your JS app locally and deploy the static files. Your Controller exposes data via API endpoints that your JS app consumes.

📖 **Complete Guides:** 
- [React Integration](docs/REACT_BUILD_INTEGRATION.md) - React 18, Vite, Webpack, CRA
- [Vue Integration](docs/VUE_BUILD_INTEGRATION.md) - Vue 2/3, Vite, Webpack, Nuxt
- Coming soon: Svelte, Angular guides

> **📌 Note:** Included modules (admin, email, auth, react, etc.) are **reference implementations** showing different approaches to common problems. After installation, **you can delete any modules** you don't need - keep only what serves your project. Each module demonstrates different techniques (middleware vs manual auth checks, cached routes, etc.) to help you choose your preferred approach. See [Module Philosophy](docs/MODULE_PHILOSOPHY.md) for details.

## 🚀 **Quick Navigation**

### **🎯 New to upMVC? Start Here:**
- **[📋 First Steps Guide](docs/FIRST-STEPS-GUIDE.md)** - Get running in 5 minutes
- **[� How-To Guide](docs/HOW-TO-GUIDE.md)** - Complete development guide
- **[❓ FAQ](docs/FAQ.md)** - Common questions and solutions
- **[� Documentation Index](docs/DOCUMENTATION-INDEX.md)** - Complete documentation map

### **🏗 Architecture & Philosophy:**
- **[🎨 Pure PHP Philosophy](docs/PHILOSOPHY_PURE_PHP.md)** - The upMVC NoFramework approach
- **[🧩 Module Philosophy](docs/MODULE_PHILOSOPHY.md)** - Modules as reference implementations
- **[🏝️ Islands Architecture](docs/ISLANDS_ARCHITECTURE_INDEX.md)** - **NEW!** Complete guide to PHP + React Islands
- **[⚛️ React Integration Patterns](docs/REACT_INTEGRATION_PATTERNS.md)** - Five ways to integrate React/Vue/Preact
- **[🔥 ReactHMR - Hot Module Reload](modules/reacthmr/README.md)** - Auto-reload without webpack
- **[📦 Integration: upMVC + PHP CRUD API Generator](docs/INTEGRATION_PHP_CRUD_API.md)** - **NEW!** Full-stack power combo guide
- **[🎯 JavaScript Framework Integration](docs/REACT_BUILD_INTEGRATION.md)** - **NEW!** Deploy React, Vue, Svelte, or any JS framework build
- **[⚛️ React Build Integration](docs/REACT_BUILD_INTEGRATION.md)** - Complete React deployment guide (Vite/Webpack/CRA)
- **[💚 Vue Build Integration](docs/VUE_BUILD_INTEGRATION.md)** - **NEW!** Vue 2/3 deployment guide (Vite/Webpack/Nuxt)
- **[💪 Architectural Strengths](docs/ARCHITECTURAL_STRENGTHS.md)** - What makes upMVC powerful
- **[🛣 Routing Capabilities](docs/ROUTING_CAPABILITIES.md)** - Understanding the routing system
- **[🔧 URL Handling Explained](docs/URL_HANDLING_EXPLAINED.md)** - Request flow and middleware

### **� Bug Fixes & Improvements:**
- **[🔐 Authentication Redirect Fix](docs/BUG_FIX_AUTH_REDIRECT.md)** - Session intended_url handling
- **[⚙️ Assignment Operator Fix](docs/BUG_FIX_AUTH_ASSIGNMENT.md)** - Comparison vs assignment
- **[🚪 Missing Exit Statements](docs/BUG_FIX_MISSING_EXIT.md)** - Proper redirect handling
- **[📤 Output Before Header Fix](docs/BUG_FIX_OUTPUT_BEFORE_HEADER.md)** - Header redirect timing
- **[🔗 Trailing Slash Fix](docs/BUG_FIX_TRAILING_SLASH.md)** - URL normalization
- **[🧹 Debug Code Cleanup](docs/CLEANUP_DEBUG_CODE.md)** - Production-ready code

### **✅ Verification & Release:**
- **[🔍 Pre-Release Verification](docs/PRE_RELEASE_VERIFICATION.md)** - Complete verification report
- **[🚀 Ready for Main](docs/READY_FOR_MAIN.md)** - Production readiness checklist
- **[📋 Verification Checklist](docs/VERIFICATION_CHECKLIST.md)** - Step-by-step validation
- **[✨ Enhancements](docs/ENHANCEMENTS.md)** - Latest improvements

### **🛠 Development:**
- **[🐛 /zbug Folder](zbug/README.md)** - Debug files and development utilities (excluded from Git)

Demo: https://upmvc.com/demo/
	

Rasmus Lerdorf: PHP NoFrameworks all suck!	


<a href = "https://www.youtube.com/watch?v=DuB6UjEsY_Y&ab_channel=matperino" target="_blank">Rasmus Lerdorf: PHP NoFrameworks all suck!</a>


# Use cases:
#### You can use the system as a standalone, as a library, as a library in the standalone version where it can be a module, you can also use it as a standalone in the standalone version /shop /blog /app /anything else - in this way, you split your app into multiple apps(shop, blog, app, anything else as separate instances of upMVC) each with their modules connected to the same or different endpoints.

# 📦 Installation

## Option 1: Install as a Library (Recommended for existing projects)

Add upMVC to your existing project in **4 simple steps:**

```bash
# Step 1: Install via Composer
composer require bitshost/upmvc
# Alternative versions:
# composer require bitshost/upmvc:^1.0  (recommended - all 1.x updates)
# composer require bitshost/upmvc:dev-main  (bleeding edge - risky!)

# Step 2: Copy essential files to project root
copy vendor/bitshost/upmvc/index.php .
copy vendor/bitshost/upmvc/.htaccess .

# Step 3: Create etc folder and copy .env configuration
mkdir etc
copy vendor/bitshost/upmvc/etc/.env etc/.env

# Step 4: Configure your environment
# Edit etc/.env with required settings:
# - SITE_PATH=/your-folder-name (e.g., /myproject)
# - DOMAIN_NAME=localhost (or your domain)
# 
# Database configuration is optional (uses etc/ConfigDatabase.php fallback)
# - Configure only when your modules need database access
# - Framework works without database for static/API projects
```

**That's it!** 🎉 Run with:
```bash
php -S localhost:8080
```

**Visit:** `http://localhost:8080` - All 16 modules will be automatically loaded and registered!

## Option 2: Install as a Standalone Project (Even Simpler!)

Create a complete upMVC project in **3 simple steps:**

```bash
# Step 1: Create project
composer create-project bitshost/upmvc yourProjectName
# Or in current directory:
# composer create-project bitshost/upmvc .

# Step 2: Navigate to project
cd yourProjectName

# Step 3: Configure etc/.env
# Edit these 2 required settings:
# - SITE_PATH=/yourProjectName (or empty if root)
# - DOMAIN_NAME=http://localhost
# 
# Database settings are optional because upMVC has smart fallbacks:
# - If .env database settings are missing, it uses etc/ConfigDatabase.php
# - Framework will work even without database (for static/API projects)
# - Configure database only when you need it for your modules
```

**That's it!** 🎉 Run with:
```bash
php -S localhost:8081
```

**Visit:** `http://localhost:8081/yourProjectName` - Complete framework with all modules ready!

**Note:** Everything is included - no copying files needed! Just configure `.env` and run.

**Optional - Keep dependencies updated:**
```bash
# Update autoloader when adding new modules
composer dump-autoload

# Update dependencies
composer update
```


## ⚙️ Configuration

upMVC uses a smart **layered configuration system** with automatic fallbacks:

### Primary Configuration: `.env` file
Edit `/etc/.env` for environment-specific settings:
- **Required:** `SITE_PATH`, `DOMAIN_NAME`, `APP_ENV`
- **Optional:** Database, mail, cache, session settings

### Fallback System
If `.env` is missing or incomplete, upMVC automatically falls back to:
- **`/etc/Config.php`** - Base URL, site paths, environment settings
- **`/etc/ConfigDatabase.php`** - Database connection parameters (fallback credentials)
- **`/modules/mail/MailController.php`** - PHPMailer SMTP configuration

**Why fallback?** Your framework works even without `.env` - perfect for:
- Quick testing and development
- Static sites or API-only projects
- Gradual configuration as you add features

💡 **Tip:** Start with just `SITE_PATH` and `DOMAIN_NAME`, add database later when needed!

#
		
## 🛣️ Routing System

upMVC offers flexible routing at multiple levels:

1. **Global Routes** → `/etc/Routes.php` - Application-wide routes
2. **Module Routes** → `/modules/yourmodule/routes/Routes.php` - Module-specific routes
3. **Module Initialization** → `/etc/InitMods.php` - Register module routes
4. **Namespace Registration** → `composer.json` - Add PSR-4 autoload entries

```json
"autoload": {
    "psr-4": {
        "YourModule\\": "modules/yourmodule/"
    }
}
```

**After adding namespaces:** Run `composer dump-autoload`

### Quick Start: Add Your Module Routes

1. Create your routes file: `/modules/yourmodule/routes/Routes.php`
2. Define your routes using `$this->addRoute()` method
3. Register in `/etc/InitMods.php`: `$initRoutes->yourmodule();`
4. **That's it** - upMVC handles the rest automatically

**Example:** See `/modules/test/routes/Routes.php` for reference implementation
    

#
Note: 
#
A friendly URL is a short and simple web address that redirects to a longer web address. Friendly URLs are called Aliases in Sitecore.
#
We achieve this by combining some .htacces rules with module routes.
Check modules/test/routes/Routes.php and the .htaccess file - you will notice the rules established in the.htaccess file for these specific routes - you may build as many as you like.

#

<img width="482" alt="Screenshot 2024-02-14 141414" src="https://github.com/upMVC/upMVC/assets/23263143/7494c92d-5fb8-4246-9e1a-12cd08edf21c">

#

<img width="550" alt="Screenshot 2024-02-14 141435" src="https://github.com/upMVC/upMVC/assets/23263143/f0c30024-f382-405d-8c75-880b9fd385d7">

#
In the same file, modules/test/routes/Routes.php, you will see for demonstration purposes how you may handle a large number of URLs with parameters (such as an idProduct) in a very straightforward way.

#

<img width="550" alt="Screenshot 2024-02-14 142531" src="https://github.com/upMVC/upMVC/assets/23263143/d5e155b2-92f8-4034-9fc8-1267efdbbf23">

#


#
#

# Steps
#
 - Edit /etc/Config.php, /etc/ConfigDatabase.php, /modules/mail/MailController.php with your data.
 - Make your module in the MVC style (model, view, controller).
 - You may or may not wish to utilize BASE MODEL, BASE VIEW and BASE CONTROLLER from the common/bmvc subdirectory.
 - BaseModel contains all of the data required for CRUD OPERATIONS; simply expand it in your module model and you have a CRUD ready-made module; see example module modules/user.
 - Make a distinctive namespace for each module
 - Your module routes should be kept under modules/YourModule/routes - file Routes.php
 - Because these routes should be presented to Router, you must provide their namespace to InitMods.php and initialize your module routes. 
 - Don't forget to update composer.json with your new namespaces for your module and routes, as well as refresh composer from the terminal:
 - composer  dump-autoload
 - php composer.phar dump-autolad
 - setup your PHPMailer - mail/MailController.php

### You have more than one method of accomplishing things in example modules, upMVC - don't enforce RULES like others do, but respect architecture models MVC, MMVC, and pure PHP and OOP programming rules.

#
#

# The Names Convention
#
## Considering recommendations:
 - Model, View, Controller - will be called without using module name in their name. For example, module name = books:
 - Model.php - class Model; View.php - class View; Controller.php - class Controller;
 - and make a distinctive namespace for each module - namespace Modulename - e.g. Books;
 - Your module routes should be kept under modules/yourmodule/routes - file Routes.php: 
   - Routes.php class Routes in folder /modules/books/routes
   - namespace Modulename\Routes, e.g. Books\Routes
#
#
##
## The provided modules (Mail and Authentication) are for illustrative purposes only. You can safely delete them, as well as any other existing modules. The goal is to demonstrate the modularity of the system and how you can create your own custom modules to suit your specific project needs.

##

#
Diagram:
![upMVC-Diagram](https://github.com/BitsHost/upMVC/assets/23263143/b3d2ff6c-bff5-41c8-9dad-a08d1b7ad6c5)

 File Structure:




![upMVC-FileStructure ](https://github.com/BitsHost/upMVC/assets/23263143/b1f92106-476a-45ee-9462-9b562edfe777)


#
#
#
### "Many noFrameworks may look very appealing at first glance because they seem to reduce web application development to a couple of trivial steps leading to some code generation and often automatic schema detection, but these same shortcuts are likely to be your bottlenecks as well since they achieve this simplicity by sacrifizing flexibility and performance."

<a href="https://toys.lerdorf.com/the-no-framework-php-mvc-framework" target="_blank">All NoFramweworks: "achieve this simplicity by sacrifizing flexibility and performance" Rasmus Lerdorf</a>



upMVC - MMVC, PHP MVC with modules. Modular MVC(Model, View, Controller) derive from Hierarchical Model‐View‐Controller (HMVC).	
											

Introducing MODULAR MVC - Empowering Your Development

In the realm of modern noFrameworks, it often feels like they do everything except what truly matters. These noFrameworks tend to add layers of abstraction that demand you to learn new skills and pathways whenever you decide to switch. They also tend to clutter themselves with superfluous options, solving simple problems in needlessly convoluted ways. 

Consider PHP, including its blade templating engine. Why introduce yet another template engine when PHP is already equipped for the task? Delving into a new noFramework often necessitates a substantial relearning effort, pushing you far beyond your existing PHP knowledge.

So, why should you choose MMVC?

MMVC, standing for Modular Model View Controller, is not about reinventing the wheel. Instead, it's about optimizing the use of exceptional components. It offers a structured, straightforward approach, and its versatility proves invaluable for project management and development.

But why MMVC specifically?

1. **Modularity:** MMVC allows you to work on a module without impacting the rest of your project. Modules can be interchanged and integrated seamlessly, enhancing your development agility.

2. **Language Freedom:** Perhaps most importantly, you have the freedom to write your modules in your preferred language, whether it's PHP, JS, PYTHON, or modern technologies like TS, React, Vue, Preact. There are no constraints on your creativity.

3. **Development-Centric:** MMVC was designed with development in mind. You can steer your project in any direction you desire, utilizing your own autoloader or composer autoload. Composer/packagist usage is optional, not obligatory.
4. **"Islands"** of Interactivity: Within this PHP-generated HTML, you strategically place interactive components built with noFrameworks like React, Vue, Preact or Svelte. These components handle dynamic elements, such as user interactions, real-time updates, and animations. Read here: <a href="https://upmvc.com/Blog/The-Rise-of-%22PHP-Islands%22:-A-Hybrid-Approach-to-Web-Development/#wbb1" target="_blank">The Rise of "PHP Islands": A Hybrid Approach to Web Development</a>

What truly sets MMVC apart is its ability to harness the latest PHP capabilities without constraint. No more endless loops, as this noFramework liberates your development possibilities.

##
BitsHost Team

