# upMVC Routing Capabilities & Advanced URL Patterns

## 🚀 The Power of .htaccess + upMVC Routing

The combination of Apache `.htaccess` rules and upMVC's routing system creates **endless possibilities** for sophisticated URL structures, enabling complex e-commerce scenarios, API endpoints, and user-friendly SEO patterns.

---

## 📁 Current .htaccess Implementation

### Basic Pattern-Based Routing
```apache
# Simple parameter extraction
RewriteRule ^test-([\w\d~%.:_\-]+)$ test?param=$1 [NC]

# Multi-level parameters
RewriteRule ^test-([\w\d~%.:_\-]+)/([\w\d~%.:_\-]+)$ test?param=$1&another=$2 [NC]

# Module-specific patterns
RewriteRule ^moda-page-([\w\d~%.:_\-]+)$ moda-page?param=$1 [NC]
```

### Module-Specific Operations
```apache
# User ORM CRUD operations
RewriteRule ^usersorm/edit/([\w\d~%.:_\-]+)$ usersorm/edit?param=$1 [NC]
RewriteRule ^usersorm/delete/([\w\d~%.:_\-]+)$ usersorm/delete?param=$1 [NC]
RewriteRule ^usersorm/update/([\w\d~%.:_\-]+)$ usersorm/update?param=$1 [NC]

# Final catch-all
RewriteRule (.+) index.php [QSA,L]
```

---

## 🎯 E-commerce & Complex Scenarios

### Product Catalog with Categories
```apache
# Product catalog with hierarchical categories
RewriteRule ^shop/([^/]+)/([^/]+)/([0-9]+)/?$ shop?category=$1&product=$2&id=$3 [NC,L]
# Example: /shop/electronics/smartphones/123 → shop?category=electronics&product=smartphones&id=123

# Product variants and specifications
RewriteRule ^product/([^/]+)/([^/]+)/([^/]+)/([^/]+)/?$ product?brand=$1&model=$2&variant=$3&spec=$4 [NC,L]
# Example: /product/apple/iphone-15/pro-max/256gb → product?brand=apple&model=iphone-15&variant=pro-max&spec=256gb
```

### User Management & Profiles
```apache
# User profiles with sub-sections
RewriteRule ^user/([^/]+)/([^/]+)/?$ user?username=$1&section=$2 [NC,L]
# Example: /user/johndoe/orders → user?username=johndoe&section=orders

# User actions with IDs
RewriteRule ^users/([0-9]+)/(edit|delete|view|activate)/?$ users?id=$1&action=$2 [NC,L]
# Example: /users/123/edit → users?id=123&action=edit
```

### Content Management & SEO
```apache
# Blog with date-based URLs
RewriteRule ^blog/([0-9]{4})/([0-9]{2})/([^/]+)/?$ blog?year=$1&month=$2&slug=$3 [NC,L]
# Example: /blog/2025/10/my-awesome-post → blog?year=2025&month=10&slug=my-awesome-post

# SEO-friendly pagination
RewriteRule ^([^/]+)/page/([0-9]+)/?$ $1?page=$2 [NC,L]
# Example: /products/page/5 → products?page=5

# Category filtering with pagination
RewriteRule ^([^/]+)/category/([^/]+)/page/([0-9]+)/?$ $1?category=$2&page=$3 [NC,L]
# Example: /products/category/electronics/page/3 → products?category=electronics&page=3
```

---

## 💡 Advanced Patterns & Techniques

### Multi-language Support
```apache
# Language prefix routing
RewriteRule ^([a-z]{2})/(.*)$ $2?lang=$1 [NC,L]
# Example: /es/shop/products → shop/products?lang=es

# Language-specific modules
RewriteRule ^([a-z]{2})/([^/]+)/([^/]+)/?$ $2?lang=$1&action=$3 [NC,L]
# Example: /fr/products/search → products?lang=fr&action=search
```

### API Versioning & Endpoints
```apache
# API versioning with modules and actions
RewriteRule ^api/v([0-9]+)/([^/]+)/([^/]+)/?$ api?version=$1&module=$2&action=$3 [NC,L]
# Example: /api/v2/products/search → api?version=2&module=products&action=search

# RESTful API patterns
RewriteRule ^api/([^/]+)/([0-9]+)/?$ api?resource=$1&id=$2 [NC,L]
# Example: /api/products/123 → api?resource=products&id=123
```

### File Format & Response Type Handling
```apache
# Format-specific responses
RewriteRule ^([^/]+)/([0-9]+)\.([a-z]+)$ $1?id=$2&format=$3 [NC,L]
# Examples:
# /products/123.json → products?id=123&format=json (API response)
# /products/123.html → products?id=123&format=html (HTML page)
# /products/123.xml → products?id=123&format=xml (XML feed)
# /products/123.pdf → products?id=123&format=pdf (PDF export)
```

### Conditional & Device-Specific Routing
```apache
# Mobile vs Desktop routing
RewriteCond %{HTTP_USER_AGENT} "Mobile|Android|iPhone|iPad" [NC]
RewriteRule ^products/([0-9]+)$ mobile/products?id=$1 [NC,L]
RewriteRule ^products/([0-9]+)$ desktop/products?id=$1 [NC,L]

# Bot-specific handling
RewriteCond %{HTTP_USER_AGENT} "Googlebot|Bingbot|facebookexternalhit" [NC]
RewriteRule ^(.*)$ seo-optimized/$1 [NC,L]
```

---

## 🔥 upMVC Router Integration

### Dynamic Parameter Injection
```php
// In your module routes
$router->addRoute('/shop/{category}/{product}/{id}', ShopController::class, 'viewProduct');
$router->addRoute('/user/{username}/{section}', UserController::class, 'profile');
$router->addRoute('/api/v{version}/{module}/{action}', ApiController::class, 'handle');

// Controllers automatically receive parameters
class ShopController {
    public function viewProduct($route, $method) {
        $params = $this->getRouteParams();
        // $params = ['category' => 'electronics', 'product' => 'smartphones', 'id' => '123']
        
        // Access individual parameters
        $category = $params['category'] ?? null;
        $productType = $params['product'] ?? null;
        $productId = $params['id'] ?? null;
    }
}
```

### Advanced Route Patterns
```php
// Complex e-commerce routes
$router->addRoute('/product/{brand}/{model}/{variant}/{storage}', ProductController::class, 'detailed');
$router->addRoute('/search/{query}/filter/{category}/sort/{order}', SearchController::class, 'results');
$router->addRoute('/admin/{module}/{action}/{id?}', AdminController::class, 'manage');

// API routes with versioning
$router->addRoute('/api/v{version:\d+}/{resource}/{action?}', ApiController::class, 'dispatch');
```

---

## ⚡ Real-World Complex Examples

### E-commerce Product URLs
```apache
# Hierarchical product structure
RewriteRule ^shop/([^/]+)/([^/]+)/([^/]+)/([^/]+)/([^/]+)/?$ 
shop?category=$1&subcategory=$2&brand=$3&model=$4&variant=$5 [NC,L]

# Example URLs:
# /shop/electronics/smartphones/apple/iphone-15/pro-max
# /shop/clothing/shoes/nike/air-force-1/white-size-42
# /shop/books/programming/oreilly/php-cookbook/2nd-edition
```

### Content Management System
```apache
# CMS with nested categories and actions
RewriteRule ^cms/([^/]+)/([^/]+)/([^/]+)/([0-9]+)/?$ 
cms?section=$1&category=$2&action=$3&id=$4 [NC,L]

# Example URLs:
# /cms/blog/technology/edit/123
# /cms/pages/about/publish/456
# /cms/media/images/organize/789
```

### Multi-tenant Application
```apache
# Tenant-specific routing
RewriteRule ^([^/]+)\.example\.com/(.*)$ $2?tenant=$1 [NC,L]
RewriteRule ^app/([^/]+)/([^/]+)/([^/]+)/?$ app?tenant=$1&module=$2&action=$3 [NC,L]

# Example URLs:
# /app/company-a/dashboard/analytics
# /app/company-b/users/list
# /app/company-c/reports/generate
```

---

## 🛠 Implementation Benefits

### SEO Advantages
- **Clean URLs**: `/products/laptops/dell/xps-13` vs `/index.php?module=products&category=laptops&brand=dell&model=xps-13`
- **Keyword-rich paths**: Better search engine ranking
- **Logical hierarchy**: Clear content structure

### User Experience
- **Memorable URLs**: Easy to share and bookmark
- **Predictable patterns**: Users can guess URLs
- **Breadcrumb navigation**: URL structure matches site navigation

### Development Flexibility
- **Parameter extraction**: Automatic parsing of URL components
- **Module isolation**: Each module handles its own routing patterns
- **Backward compatibility**: Old URLs can be redirected to new patterns

### Performance & Caching
- **CDN-friendly**: Static-looking URLs cache better
- **Browser caching**: Predictable URL patterns improve caching
- **Server-side optimization**: Efficient parameter parsing

---

## 🔮 Future Possibilities

This routing system enables unlimited expansion possibilities:

- **Machine learning URLs**: `/recommendations/user/123/category/electronics`
- **Real-time data**: `/live/stock/AAPL/chart/1hour`
- **Geographic routing**: `/region/europe/country/france/city/paris`
- **Time-based content**: `/events/2025/october/conferences`
- **A/B testing**: `/variant/a/product/123` vs `/variant/b/product/123`

The combination of `.htaccess` flexibility and upMVC's routing power provides a foundation for any URL structure you can imagine! 🎯

---

## 🛠 Development & Debugging

### Debug Files Location
All routing tests, debug scripts, and development utilities should be placed in the `/zbug` folder, which is automatically excluded from Git commits. This keeps the repository clean while allowing for extensive testing and debugging.

Examples:
- `/zbug/test_routing_patterns.php` - Testing new URL patterns
- `/zbug/debug_htaccess_rules.php` - Debugging rewrite rules
- `/zbug/benchmark_route_performance.php` - Performance testing

---

*This document will be continuously updated with new patterns and advanced routing techniques as they are discovered and implemented.*