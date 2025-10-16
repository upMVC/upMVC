# upMVC Philosophy: Pure PHP & Simple OOP

## Core Principle

> **"Use pure PHP and OOP. We don't need overcomplicated code to achieve simple things."**

This is the fundamental philosophy behind upMVC - a **NoFramework** approach that gives you complete freedom, simplicity, and clarity without framework constraints.

## What upMVC IS

### ✅ Pure PHP
```php
// Direct PHP - No magic
$_SESSION["logged"] = true;
$_POST['username'];
header("Location: $url");
```

### ✅ Simple OOP
```php
// Clear class structure
class Controller {
    private function login() { }
    private function logout() { }
}

// Direct instantiation - No DI container complexity
$view = new BaseView();
$model = new Model();
```

### ✅ Straightforward Logic
```php
// Simple conditional
if (isset($_SESSION["logged"]) && $_SESSION["logged"] === true) {
    header("Location: $url");
    exit;
}
```

## What upMVC is NOT

### ❌ No Dependency Injection Containers
```php
// We DON'T do this:
$container->bind('AuthService', function() {
    return new AuthService(
        new SessionManager(
            new CookieJar(
                new Encryptor()
            )
        )
    );
});

// We DO this:
$users = new Model();
```

### ❌ No Facades/Magic Methods
```php
// We DON'T do this:
Auth::user()->isAdmin();
Session::flash('message', 'Success');

// We DO this:
$_SESSION['username'];
$_SESSION['logged'] = true;
```

### ❌ No Abstract Complexity
```php
// We DON'T do this:
interface AuthenticatableInterface {
    public function getAuthIdentifierName();
    public function getAuthIdentifier();
    public function getAuthPassword();
    public function getRememberToken();
    public function setRememberToken($value);
    public function getRememberTokenName();
}

// We DO this:
$stmt = $users->readUserLogin();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
```

## Real Examples from upMVC

### Authentication (Simple & Clear)

```php
// Login logic - Pure PHP
private function login()
{
    $users = new Model();
    
    if ($_POST) {
        $users->username = $_POST['username'];
        $users->password = $_POST['password'];
        $stmt = $users->readUserLogin();
        
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $_SESSION["logged"] = true;
            $redirectUrl = $_SESSION['intended_url'] ?? BASE_URL;
            unset($_SESSION['intended_url']);
            
            $this->html->validateToken($redirectUrl);
            exit;
        }
    }
}
```

**Why this is good:**
- ✅ Easy to read
- ✅ Easy to debug
- ✅ No hidden magic
- ✅ Direct PHP
- ✅ Clear flow

### Routing (Straightforward)

```php
// Routes.php - Simple class
class Routes
{
    public function startRoutes(string $reqRoute, string $reqMet, ?string $reqURI = null): void
    {
        $this->registerRoutes();
        $this->router->dispatcher($reqRoute, $reqMet, $reqURI);
    }
}
```

**Why this is good:**
- ✅ No route caching complexity
- ✅ No route model binding magic
- ✅ Direct method calls
- ✅ Three simple parameters

### Middleware (Minimal)

```php
// AuthMiddleware - Pure PHP
public function handle(array $request, callable $next)
{
    $route = $request['route'] ?? '';
    
    if ($this->requiresAuth($route)) {
        if (!isset($_SESSION['logged'])) {
            $_SESSION['intended_url'] = $request['uri'];
            header('Location: /auth');
            exit;
        }
    }
    
    return $next($request);
}
```

**Why this is good:**
- ✅ Simple array, not request object
- ✅ Direct session access
- ✅ Clear logic
- ✅ No middleware stack complexity

## Comparison: upMVC NoFramework vs Heavy Frameworks

### Laravel Framework Example (Complex)
```php
// Too much abstraction
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard')
        ->middleware('can:view-dashboard');
});

class DashboardController extends Controller
{
    public function __construct(
        protected UserRepository $users,
        protected AuthManager $auth,
        protected ViewFactory $view
    ) {}
    
    public function index(Request $request): View
    {
        $user = Auth::user();
        return view('dashboard', compact('user'));
    }
}
```

### upMVC Example (Simple)
```php
// Clean and direct
$router->addRoute('/dashboard', Dashboard\Controller::class, 'display', ['auth']);

class Controller
{
    public function display($reqRoute, $reqMet)
    {
        if (!isset($_SESSION['logged'])) {
            header('Location: /auth');
            exit;
        }
        
        $view = new BaseView();
        $this->html = new View();
        $view->startHead("Dashboard");
        $this->html->showDashboard();
        $view->endBody();
    }
}
```

## Benefits of Simple Code

### 1. Easy to Learn
```php
// Anyone who knows PHP can understand this:
if ($_POST) {
    $users->username = $_POST['username'];
    $stmt = $users->readUserLogin();
}
```

### 2. Easy to Debug
```php
// Direct path - no magic
$users = new Model();  // ← You know exactly what's happening
$stmt = $users->readUserLogin();  // ← Clear method call
$row = $stmt->fetch(PDO::FETCH_ASSOC);  // ← Standard PDO
```

### 3. Easy to Customize
```php
// Want to change behavior? Just edit the method:
private function login()
{
    // Add your custom logic here
    // No need to understand framework internals
}
```

### 4. No Hidden Dependencies
```php
// You see all dependencies:
$view = new BaseView();
$users = new Model();
$mail = new MailController();

// Not buried in a container somewhere
```

## When Complexity is OK

### Middleware Pipeline ✅
```php
// This adds value without obscuring logic
$middlewareManager->execute($route, $request, function($request) {
    return $this->callController($class, $method, $route);
});
```

**Why:** Provides flexibility while keeping each middleware simple

### Config Management ✅
```php
// Centralized config is useful
define('BASE_URL', Config::DOMAIN_NAME . Config::SITE_PATH);
```

**Why:** One place for settings, but still simple PHP

## Anti-Patterns to Avoid

### ❌ Service Locator Pattern
```php
// DON'T
$user = app('UserService')->find($id);

// DO
$user = new Model();
$user->findById($id);
```

### ❌ Active Record Bloat
```php
// DON'T
$user = User::with('posts.comments.author')
    ->whereHas('subscriptions', function($q) {
        $q->where('active', true);
    })
    ->firstOrFail();

// DO
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();
```

### ❌ Magic Getters/Setters
```php
// DON'T
$user->name = 'John';  // Triggers __set() magic

// DO
$user->setName('John');  // Explicit method
```

## The upMVC Way

### Core Values

1. **Explicit over Implicit**
   - `$_POST['username']` not `$request->username`
   - `new Model()` not `app(Model::class)`

2. **Simple over Clever**
   - `if/else` not reflection/magic
   - Direct calls not dynamic resolution

3. **Clear over Compact**
   - Readable code over one-liners
   - Explicit flow over hidden abstractions

4. **Standard PHP over Framework Magic**
   - `$_SESSION` not facades
   - `header()` not response objects
   - `PDO` not heavy ORMs

### Example: The Right Level of Abstraction

```php
// ✅ GOOD - Clear abstraction that helps
class Router
{
    public function addRoute($route, $class, $method) {
        $this->routes[$route] = ['class' => $class, 'method' => $method];
    }
}

// ❌ BAD - Over-abstraction that obscures
class RouteCollection implements ArrayAccess, Countable, IteratorAggregate
{
    public function match(Request $request): Route {
        return $this->repository->findByRequest($request);
    }
}
```

## Conclusion

upMVC is a **NoFramework** - following **Rasmus Lerdorf's philosophy**: "PHP frameworks all suck!"

We give you freedom and keep it simple:
- ✅ Pure PHP - `$_SESSION`, `$_POST`, `header()`
- ✅ Direct OOP - `new Class()`, clear methods
- ✅ Standard patterns - MVC, not magical architectures
- ✅ Easy to understand - Read the code, know what it does
- ✅ Easy to debug - No hidden layers
- ✅ Easy to customize - Change what you need

**Remember:** If you need to read framework documentation to understand how basic PHP works, the framework is too complex.

upMVC: **Just PHP, done right.** 🎯
