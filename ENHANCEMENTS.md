# upMVC Core System Enhancements

This document outlines the major enhancements made to the upMVC core system to improve functionality, security, performance, and developer experience.

## 🚀 Enhancement Overview

### 1. Middleware System
**Location**: `etc/Middleware/`

A comprehensive middleware system for request/response processing:

- **MiddlewareInterface**: Standard interface for all middleware
- **MiddlewareManager**: Pipeline management for middleware execution
- **AuthMiddleware**: Authentication checks with protected route patterns
- **LoggingMiddleware**: Request logging with execution time tracking
- **CorsMiddleware**: Cross-Origin Resource Sharing header management

**Usage Example**:
```php
// In your controller or bootstrap
$router = new Router();
$middlewareManager = $router->getMiddlewareManager();

// Add global middleware (runs on all routes)
$middlewareManager->addGlobal(new LoggingMiddleware());
$middlewareManager->addGlobal(new CorsMiddleware(['origins' => ['*']]));

// Add route-specific middleware
$middlewareManager->addForRoute('/admin/*', new AuthMiddleware(['/admin/*']));
```

### 2. Dependency Injection Container
**Location**: `etc/Container/`

A powerful dependency injection container for better testability and loose coupling:

- **Container**: Main DI container with automatic dependency resolution
- **ServiceProviderInterface**: Interface for service providers

**Usage Example**:
```php
$container = new Container();

// Bind interfaces to implementations
$container->bind(DatabaseInterface::class, MySQLDatabase::class);

// Register singletons
$container->singleton(Logger::class);

// Resolve dependencies automatically
$userService = $container->make(UserService::class); // Auto-injects dependencies
```

### 3. Enhanced Error Handling
**Location**: `etc/Exceptions/` and `common/errors/`

Comprehensive error handling with custom exceptions and user-friendly error pages:

- **Custom Exceptions**: Structured exception hierarchy with HTTP status codes
- **ErrorHandler**: Global error and exception handler with logging
- **Error Pages**: Beautiful error pages for 404, 500, and 403 errors

**Available Exceptions**:
- `RouteNotFoundException` (404)
- `AuthenticationException` (401)
- `AuthorizationException` (403)
- `ValidationException` (422)
- `DatabaseException` (500)
- `ConfigurationException` (500)

**Usage Example**:
```php
// Throw custom exceptions
throw new ValidationException('Invalid input', ['email' => 'Email is required']);

// Global error handler automatically formats and logs errors
$errorHandler = new ErrorHandler($debug = true);
$errorHandler->register();
```

### 4. Enhanced Configuration System
**Location**: `etc/Config/`

Environment-based configuration management:

- **Environment**: `.env` file support with automatic creation
- **ConfigManager**: Dot notation configuration access

**Features**:
- Environment variable support
- Automatic `.env` file creation
- Configuration validation
- Dot notation access (`config.get('database.host')`)

**Usage Example**:
```php
// Load environment and configuration
ConfigManager::load();

// Access configuration with dot notation
$dbHost = ConfigManager::get('database.connections.mysql.host');
$appDebug = ConfigManager::get('app.debug', false);

// Environment helpers
if (Environment::isDevelopment()) {
    // Development-specific code
}
```

### 5. Caching System
**Location**: `etc/Cache/`

Flexible caching system with multiple drivers:

- **CacheInterface**: Standard caching interface
- **FileCache**: File-based cache implementation
- **ArrayCache**: In-memory cache for testing
- **CacheManager**: Cache store management
- **TaggedCache**: Cache invalidation with tags

**Usage Example**:
```php
// Basic caching
CacheManager::put('user:123', $userData, 3600);
$userData = CacheManager::get('user:123');

// Remember pattern
$expensiveData = CacheManager::remember('expensive_calculation', function() {
    return performExpensiveCalculation();
}, 3600);

// Tagged caching for invalidation
CacheManager::tags(['users', 'profiles'])->put('user:123', $userData);
CacheManager::tags(['users'])->flush(); // Invalidate all user-related cache
```

### 6. Event System
**Location**: `etc/Events/`

Event-driven architecture for loose coupling between modules:

- **Event**: Base event class with propagation control
- **EventDispatcher**: Publish-subscribe pattern implementation
- **Pre-defined Events**: Common system and user events

**Usage Example**:
```php
$dispatcher = new EventDispatcher();

// Listen to events
$dispatcher->listen(UserRegistered::class, function($event) {
    // Send welcome email
    $userData = $event->getData();
    sendWelcomeEmail($userData['email']);
});

// Dispatch events
$dispatcher->dispatch(new UserRegistered(['email' => 'user@example.com']));

// Wildcard listeners
$dispatcher->listenWildcard('User*', function($event) {
    // Log all user-related events
    logUserActivity($event);
});
```

## 🔧 Enhanced Router Features

The Router class has been enhanced with:

- **Middleware Pipeline**: Automatic middleware execution
- **Request Context**: Rich request information passed to middleware
- **Backward Compatibility**: Legacy middleware still supported
- **Enhanced Dispatching**: Better error handling and context passing

## 📁 Updated File Structure

```
etc/
├── Middleware/
│   ├── MiddlewareInterface.php
│   ├── MiddlewareManager.php
│   ├── AuthMiddleware.php
│   ├── LoggingMiddleware.php
│   └── CorsMiddleware.php
├── Container/
│   ├── Container.php
│   └── ServiceProviderInterface.php
├── Exceptions/
│   ├── Exceptions.php
│   └── ErrorHandler.php
├── Config/
│   ├── Environment.php
│   └── ConfigManager.php
├── Cache/
│   ├── CacheInterface.php
│   ├── FileCache.php
│   └── CacheManager.php
├── Events/
│   ├── Event.php
│   └── EventDispatcher.php
├── Router.php (enhanced)
├── Start.php (enhanced)
└── Config.php (original)

common/
└── errors/
    ├── 404.php
    ├── 500.php
    └── 403.php

.env (auto-created)
composer.json (updated namespaces)
```

## 🚦 Getting Started

### 1. Update Composer Autoloader
```bash
composer dump-autoload
```

### 2. Environment Configuration
The system will automatically create a `.env` file with default values. Update it with your configuration:

```env
APP_ENV=development
APP_DEBUG=true
APP_URL=https://yourdomain.com

DB_HOST=127.0.0.1
DB_NAME=your_database
DB_USER=your_username
DB_PASS=your_password

CACHE_DRIVER=file
LOG_LEVEL=debug
```

### 3. Error Handling Setup
Error handling is automatically registered in the enhanced `Start.php`. For custom error pages, modify files in `common/errors/`.

### 4. Middleware Usage
Add middleware to your routes or globally through the enhanced router system.

## 🔄 Backward Compatibility

All enhancements maintain backward compatibility with existing upMVC applications:

- Original routing still works
- Legacy middleware system is preserved
- Existing modules continue to function
- Original configuration files are still supported

## 🎯 Benefits

### For Developers
- **Better Code Organization**: Clear separation of concerns
- **Improved Testing**: Dependency injection makes testing easier
- **Enhanced Debugging**: Better error messages and logging
- **Modern Patterns**: Industry-standard patterns and practices

### For Applications
- **Better Performance**: Caching system reduces database queries
- **Enhanced Security**: Middleware-based security checks
- **Improved Monitoring**: Request logging and error tracking
- **Scalability**: Event system allows for loose coupling

### For Deployment
- **Environment Management**: Easy configuration for different environments
- **Error Handling**: Graceful error handling with proper HTTP status codes
- **Logging**: Comprehensive logging for debugging and monitoring
- **Caching**: Built-in caching for improved performance

## 🛠 Next Steps for Implementation

1. **Review and Test**: Test the enhancements with your existing modules
2. **Migration Guide**: Gradually migrate existing code to use new features
3. **Documentation**: Create specific documentation for your use cases
4. **Custom Middleware**: Create application-specific middleware
5. **Event Listeners**: Implement event listeners for your business logic
6. **Cache Strategy**: Implement caching strategy for your data
7. **Error Monitoring**: Set up error monitoring and alerting

## 🔍 Advanced Usage

### Custom Middleware
```php
class CustomMiddleware implements MiddlewareInterface 
{
    public function handle(array $request, callable $next) 
    {
        // Pre-processing
        $response = $next($request);
        // Post-processing
        return $response;
    }
}
```

### Custom Events
```php
class OrderPlaced extends Event {}

$dispatcher->listen(OrderPlaced::class, function($event) {
    $order = $event->get('order');
    // Process order, send emails, update inventory, etc.
});
```

### Service Providers
```php
class DatabaseServiceProvider implements ServiceProviderInterface 
{
    public function register(Container $container): void 
    {
        $container->singleton(Database::class, function($container) {
            return new Database(ConfigManager::get('database'));
        });
    }
    
    public function boot(Container $container): void 
    {
        // Boot logic
    }
}
```

This enhancement package transforms upMVC into a modern, scalable PHP framework while maintaining its simplicity and flexibility principles.