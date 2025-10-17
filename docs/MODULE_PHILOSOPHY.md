# upMVC Module Philosophy

## 🎯 Modules as Reference Implementations

The modules included with upMVC (`admin`, `auth`, `email`, `react`, etc.) are **not required dependencies**. They are **reference implementations** that demonstrate different approaches to common problems.

### Core Principle

> **You can delete any or all modules after installation.**
> 
> Keep only what you need. Each module shows one way to solve a problem - choose the approach that fits your project.

---

## 🗂 What Each Module Demonstrates

### Authentication Module (`/modules/auth`)
**Purpose:** Shows basic login/logout implementation  
**Keep if:** You need simple username/password authentication  
**Can delete if:** You're using OAuth, LDAP, or external auth  

**What it teaches:**
- Session-based authentication
- Basic login forms
- Password verification
- Redirect after login

---

### Admin Module (`/modules/admin`)
**Purpose:** Demonstrates CRUD operations with **cached database routing**  
**Keep if:** You need a user management dashboard  
**Can delete if:** You don't need admin functionality  

**What it teaches:**
- Complete CRUD operations
- **Route caching** for performance
- Database-driven dynamic routes
- Flash messages
- Form validation
- Dashboard statistics

**Key Feature:** Shows how to cache routes to avoid database queries on every request. See `routes/Routes.php` for implementation.

---

### Email Module (`/modules/mail`)
**Purpose:** Shows email sending functionality  
**Keep if:** Your app sends emails  
**Can delete if:** You use external email services  

**What it teaches:**
- PHPMailer integration
- Email configuration
- Template-based emails

---

### React Modules (`/modules/react*`)
**Purpose:** Demonstrates **four different patterns** for integrating React/Vue/Preact  
**Keep if:** You want to mix PHP backend with JS frontend  
**Can delete if:** You're doing pure PHP or external SPA  

**What they teach:**
- **`react`** - CDN components (no build step)
- **`reactb`** - Built React app embedded in PHP sections
- **`reactcrud`** - Full React SPA with PHP backend
- **`reactnb`** - ES modules without build (modern approach)

See **[React Integration Patterns](REACT_INTEGRATION_PATTERNS.md)** for complete guide.

---

## 🔐 Two Authentication Approaches

upMVC demonstrates **two different ways** to protect routes. Choose the one that fits your style:

### Approach 1: Manual Session Check (Simple)

**Example:** `modules/react/Controller.php`

```php
private function main($reqMet, $reqRoute)
{
    $view = new View();
    
    // Manual check
    if (isset($_SESSION["logged"])) {
        $view->View($reqMet);
        echo $reqMet . " " .  $reqRoute . " ";
    } else {
        echo "Not Logged In! Something else.";
        header('Location: ' . BASE_URL . '/');
        exit;
    }
}
```

**Pros:**
- ✅ Simple and explicit
- ✅ Easy to understand
- ✅ Full control per method
- ✅ No additional configuration

**Cons:**
- ⚠️ Must remember to check in every method
- ⚠️ Code duplication if many protected methods

**Use when:**
- You have few protected routes
- You want explicit control
- You're learning upMVC
- You prefer simplicity over DRY

---

### Approach 2: Middleware in Start.php (Recommended)

**Configuration:** `aDiverse/Starta.php` (or `Start.php`)

```php
// Define protected route patterns
$protectedRoutes = [
    '/admin',
    '/admin/*',  // Protects all /admin/* routes
    '/dashboard',
    '/profile/*'
];

// Middleware checks before routing
foreach ($protectedRoutes as $pattern) {
    if (matchesPattern($_SERVER['REQUEST_URI'], $pattern)) {
        if (!isset($_SESSION["logged"]) || $_SESSION["logged"] !== true) {
            header('Location: ' . BASE_URL . '/auth');
            exit;
        }
        break;
    }
}

// Helper function
function matchesPattern($uri, $pattern) {
    $pattern = str_replace('*', '.*', $pattern);
    return preg_match("#^" . $pattern . "#", $uri);
}
```

**Controller:** `modules/admin/Controller.php`

```php
public function display($reqRoute, $reqMet)
{
    // No auth check needed - Start.php middleware already protected
    $this->handleRoute($reqRoute, $reqMet);
}

private function handleRoute($reqRoute, $reqMet)
{
    switch ($reqRoute) {
        case '/admin':
            $this->dashboard();
            break;
        case '/admin/users':
            $this->listUsers();
            break;
        // ... more routes
    }
}
```

**Pros:**
- ✅ Centralized protection in `Start.php`
- ✅ Protects routes BEFORE controllers run
- ✅ Pattern-based (protect `/admin/*` with one line)
- ✅ Clean controller code (no auth logic)
- ✅ Easy to manage all protected routes

**Cons:**
- ⚠️ Must remember to add patterns to `$protectedRoutes`
- ⚠️ Pattern matching can be tricky

**Use when:**
- You want middleware-style protection
- You have multiple protected modules
- You prefer centralized security
- You want clean controller separation

---

### Approach 3: Per-Route Middleware (Advanced)

For more granular control, implement route-specific middleware:

```php
// In routes/Routes.php
class Routes
{
    public static function register($router)
    {
        $router->addRoute('/admin', Controller::class, 'display')
            ->middleware(function() {
                if (!isset($_SESSION["logged"])) {
                    header('Location: ' . BASE_URL . '/auth');
                    exit;
                }
            });
    }
}
```

**Pros:**
- ✅ Granular per-route control
- ✅ Can chain multiple middlewares
- ✅ Different auth per route
- ✅ Framework-like elegance

**Cons:**
- ⚠️ Most complex approach
- ⚠️ Requires Router middleware support
- ⚠️ More abstraction layers

**Use when:**
- Routes need different auth levels
- You want complex middleware chains
- You're building framework-like features
- You need maximum flexibility

---

## 🎨 Choose Your Style

| Approach | Complexity | Location | Use Case | Example |
|----------|-----------|----------|----------|---------|
| **Manual Check** | Low | Controller methods | Few protected routes | `react` module |
| **Start.php Middleware** | Medium | `Start.php` `$protectedRoutes` | Module-wide patterns | `admin` module (recommended) |
| **Per-Route Middleware** | High | Route definitions | Granular per-route control | Custom Router implementation |

**All three are valid!** upMVC doesn't force any approach - choose what works for you.

---

## 🧹 Minimal Installation

Want the absolute minimum? Here's what you can keep:

### Minimal Auth Setup
```
/modules/
  └── auth/          ← Keep for login functionality
      └── Controller.php
      └── View.php
      └── routes/Routes.php
```

**Delete everything else!**

### No Auth Needed?
```
/modules/
  └── (empty or your custom modules only)
```

Delete auth, admin, email, react - everything!

---

## 📚 Learning Path

### Step 1: Study Reference Modules
Look at included modules to see different approaches:
- `auth` - Basic authentication
- `admin` - CRUD + route caching
- `react*` - Four JS framework integration patterns (no build, with build, ES modules)

### Step 2: Choose Your Approach
Pick the patterns that fit your project:
- Manual auth checks or middleware?
- Cached routes or pattern matching?
- Pure PHP or hybrid JS?

### Step 3: Clean Up
Delete modules you don't need:
```bash
# Example: Keep only auth
rm -rf modules/admin modules/email modules/react modules/userorm
```

### Step 4: Build Your Modules
Create your own modules using the patterns you learned:
```bash
mkdir -p modules/yourmodule/{routes,templates}
# Copy structure from reference modules
```

---

## 🎯 Key Takeaways

1. **Reference, Not Requirements**
   - Modules are examples, not dependencies
   - Delete what you don't need

2. **Multiple Valid Approaches**
   - Manual checks vs middleware
   - Cached routes vs pattern matching
   - Choose what fits your style

3. **Freedom to Choose**
   - No "upMVC way" - pick your own
   - Mix approaches if needed
   - Framework features are optional

4. **Learn by Example**
   - Study included modules
   - See different solutions
   - Adopt what works for you

---

## 🔗 Related Documentation

- **[Routing Strategies](routing/ROUTING_STRATEGIES.md)** - Route caching vs pattern matching
- **[React Integration Patterns](REACT_INTEGRATION_PATTERNS.md)** - Four ways to integrate React/Vue/Preact
- **[Admin Module](../modules/admin/README.md)** - CRUD + cached routes example
- **[Auth Module](../modules/auth/README.md)** - Basic authentication example
- **[Pure PHP Philosophy](PHILOSOPHY_PURE_PHP.md)** - upMVC design principles

---

## 💡 Philosophy

> **upMVC gives you tools and examples, not rules.**
> 
> Look at the included modules, learn from them, then **delete them and build your own way**.
> 
> That's the NoFramework philosophy.

