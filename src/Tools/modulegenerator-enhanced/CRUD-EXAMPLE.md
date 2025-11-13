# CRUD Module Generation Example - Products Management

This document provides a complete walkthrough of creating a Products CRUD module using the enhanced module generator.

## Prerequisites

1. **Enhanced upMVC System**
   - `InitModsImproved.php` installed in `etc/`
   - Environment configuration in `etc/.env`
   - Composer autoloader configured

2. **Database Setup**
   ```sql
   -- Ensure you have a database configured in .env
   -- DB_NAME=upmvc
   -- DB_USER=root
   -- DB_PASS=your_password
   ```

3. **Environment Configuration**
   ```properties
   # In etc/.env
   APP_ENV=development
   ROUTE_SUBMODULE_DISCOVERY=true
   ROUTE_USE_CACHE=false  # For development
   ROUTE_DEBUG_OUTPUT=true  # For debugging
   ```

## Step-by-Step Generation

### Step 1: Navigate and Run Generator
```bash
cd tools/modulegenerator-enhanced/
php generate-module.php
```

### Step 2: Interactive Configuration
The generator will prompt you for configuration. Here's what to enter:

```
📝 Enter module name: Products
📋 Select module type: crud
📝 Define fields for your CRUD module:

Field 1: name:VARCHAR(255):text
Field 2: description:TEXT:textarea  
Field 3: price:DECIMAL(10,2):number
Field 4: category:VARCHAR(100):text
Field 5: sku:VARCHAR(50):text
Field 6: status:ENUM('active','inactive'):select
Field 7: [Press Enter to finish]

🗄️ Create database table automatically? [Y/n]: y
🔒 Enable middleware integration? [Y/n]: y
📦 Create submodule structure? [y/N]: y
```

### Step 3: Generation Output
You'll see output like this:

```
🚀 Generating enhanced module 'Products'...
🔍 Checking prerequisites...
   ✅ InitModsImproved.php found
   ✅ Environment class available
   ✅ Found 0 existing modules

📁 Created directory: modules/Products
📁 Created directory: modules/Products/routes
📁 Created directory: modules/Products/views
📁 Created directory: modules/Products/views/layouts
📁 Created directory: modules/Products/etc
📁 Created directory: modules/Products/assets
📁 Created directory: modules/Products/assets/css
📁 Created directory: modules/Products/assets/js
📁 Created directory: modules/Products/modules

📄 Generated: modules/Products/Controller.php
📄 Generated: modules/Products/Model.php
📄 Generated: modules/Products/View.php
📄 Generated: modules/Products/routes/Routes.php
🔄 Routes will be auto-discovered by InitModsImproved.php (no manual registration needed)
📄 Generated: modules/Products/views/layouts/header.php
📄 Generated: modules/Products/views/layouts/footer.php
📄 Generated: modules/Products/views/index.php
📄 Generated: modules/Products/assets/css/style.css
📄 Generated: modules/Products/assets/js/script.js
📄 Generated: modules/Products/etc/config.php
📄 Generated: modules/Products/etc/api-docs.md

📦 Creating submodule structure...
✅ Created example submodule with auto-discovery support

✅ Database table 'products' created successfully
📝 Updated composer.json autoload configuration

✅ Enhanced module generation completed successfully!
🔍 Module will be auto-discovered by InitModsImproved.php
```

### Step 4: Update Autoloader
```bash
composer dump-autoload
```

## Generated Files Overview

### Core MVC Files

#### `modules/Products/Controller.php`
```php
<?php
namespace Products;

use Common\Bmvc\BaseController;

class Controller extends BaseController
{
    private $model;
    private $view;
    private $table = 'products';
    private $moduleRoute = BASE_URL . '/products';

    public function __construct()
    {
        $this->model = new Model();
        $this->view = new View();
    }

    public function display($reqRoute, $reqMet): void
    {
        if (isset($_SESSION["username"])) {
            $this->selectAction($reqMet);
        } else {
            header('Location: ' . BASE_URL . '/auth');
            exit;
        }
    }

    // Full CRUD methods included...
    // - index() - List products with pagination
    // - showCreateForm() - Display create form
    // - create() - Handle product creation
    // - showEditForm() - Display edit form  
    // - update() - Handle product updates
    // - delete() - Handle product deletion
    // - api() - RESTful API endpoints
}
```

#### `modules/Products/Model.php`
```php
<?php
namespace Products;

use Common\Bmvc\BaseModel;

class Model extends BaseModel
{
    protected $table = 'products';
    protected $enableCaching = true;

    // Enhanced methods included...
    // - createRecord() - Create with validation
    // - updateRecord() - Update with validation
    // - getRecordsWithSearch() - Search and pagination
    // - getTotalCount() - Count for pagination
    // - validateData() - Input validation
    // - getStatistics() - Dashboard stats
}
```

#### `modules/Products/routes/Routes.php`
```php
<?php
namespace Products\Routes;

use Products\Controller;

class Routes
{
    public function Routes($router): void
    {
        // Main CRUD routes
        $router->addRoute('/products', Controller::class, 'display');
        $router->addRoute('/products/api', Controller::class, 'api');
        $router->addRoute('/products/search', Controller::class, 'search');
        
        // Enhanced: Environment-specific routes
        if (\upMVC\Config\Environment::isDevelopment()) {
            $router->addRoute('/products/debug', Controller::class, 'debug');
        }
    }
}
```

### View Templates

#### `modules/Products/views/index.php`
Complete product listing with:
- Bootstrap table with responsive design
- Pagination controls
- Search functionality
- Create/Edit/Delete action buttons
- Flash message display
- Empty state handling

#### `modules/Products/views/create.php`
Product creation form with:
- Bootstrap form styling
- Field validation
- Error display
- Cancel/Save actions

#### `modules/Products/views/edit.php`
Product editing form with:
- Pre-populated values
- Validation
- Update/Cancel actions

### Frontend Assets

#### `modules/Products/assets/css/style.css`
```css
/* Enhanced Products Module Styles */
:root {
    --Products-primary: #007bff;
    --Products-success: #28a745;
    --Products-danger: #dc3545;
}

.Products-card {
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    border: none;
    border-radius: 12px;
    transition: transform 0.2s ease;
}

.Products-card:hover {
    transform: translateY(-2px);
}

/* Responsive design, animations, loading states included... */
```

#### `modules/Products/assets/js/script.js`
```javascript
class EnhancedProductsModule {
    constructor() {
        this.moduleName = 'Products';
        this.version = '2.0-enhanced';
        this.apiEndpoint = '/products/api';
        this.init();
    }
    
    // Enhanced features:
    // - Form validation
    // - AJAX CRUD operations
    // - Loading states
    // - Error handling
    // - Debug information
    // - Auto-refresh functionality
}
```

## Using the Generated Module

### 1. Access the Web Interface

**Product Listing:**
```
http://localhost/upMVC/products
```

**Features Available:**
- View all products in a responsive table
- Pagination (10 items per page)
- Search products by name or description
- Create new products
- Edit existing products
- Delete products (with confirmation)
- Flash messages for success/error feedback

### 2. API Endpoints

**Get All Products:**
```bash
curl -X GET "http://localhost/upMVC/products/api"
```

**Get Single Product:**
```bash
curl -X GET "http://localhost/upMVC/products/api?id=1"
```

**Create Product:**
```bash
curl -X POST "http://localhost/upMVC/products/api" \
     -H "Content-Type: application/json" \
     -d '{
       "name": "Sample Product",
       "description": "Product description",
       "price": 29.99,
       "category": "Electronics",
       "sku": "SP001",
       "status": "active"
     }'
```

**Update Product:**
```bash
curl -X PUT "http://localhost/upMVC/products/api" \
     -H "Content-Type: application/json" \
     -d '{
       "id": 1,
       "name": "Updated Product",
       "price": 39.99,
       "status": "active"
     }'
```

**Delete Product:**
```bash
curl -X DELETE "http://localhost/upMVC/products/api" \
     -H "Content-Type: application/json" \
     -d '{"id": 1}'
```

### 3. Database Operations

The module automatically creates this table:
```sql
CREATE TABLE IF NOT EXISTS `products` (
  id INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `category` VARCHAR(100) NOT NULL,
  `sku` VARCHAR(50) NOT NULL,
  `status` ENUM('active','inactive') NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Sample Data Insert:**
```sql
INSERT INTO products (name, description, price, category, sku, status) VALUES
('Laptop Pro', 'High-performance laptop for professionals', 1299.99, 'Electronics', 'LP001', 'active'),
('Wireless Mouse', 'Ergonomic wireless mouse with precision tracking', 29.99, 'Accessories', 'WM001', 'active'),
('Mechanical Keyboard', 'RGB backlit mechanical keyboard', 89.99, 'Accessories', 'MK001', 'active');
```

## Customization Examples

### 1. Adding Validation Rules

Edit `modules/Products/Model.php`:
```php
private function validateData(array $data): bool
{
    // Custom validation rules
    if (empty($data['name']) || strlen($data['name']) < 3) {
        return false;
    }
    
    if (!is_numeric($data['price']) || $data['price'] <= 0) {
        return false;
    }
    
    if (!in_array($data['status'], ['active', 'inactive'])) {
        return false;
    }
    
    // SKU uniqueness check
    if ($this->isSkuExists($data['sku'], $data['id'] ?? null)) {
        return false;
    }
    
    return true;
}
```

### 2. Adding Categories Submodule

Generate a categories submodule:
```bash
php generate-module.php
# Select: submodule
# Parent module: Products  
# Name: Categories
```

This creates: `modules/Products/modules/Categories/` with auto-discovery.

### 3. Adding Custom Routes

Edit `modules/Products/routes/Routes.php`:
```php
public function Routes($router): void
{
    // Existing routes...
    $router->addRoute('/products', Controller::class, 'display');
    $router->addRoute('/products/api', Controller::class, 'api');
    
    // Custom routes
    $router->addRoute('/products/export', Controller::class, 'export');
    $router->addRoute('/products/import', Controller::class, 'import');
    $router->addRoute('/products/bulk-delete', Controller::class, 'bulkDelete');
    $router->addRoute('/products/statistics', Controller::class, 'statistics');
}
```

### 4. Environment-Specific Features

The module respects environment settings:

**Development:**
```properties
# In .env
APP_ENV=development
ROUTE_DEBUG_OUTPUT=true
PRODUCTS_CACHE_TTL=0  # No caching in dev
```

**Production:**
```properties
# In .env  
APP_ENV=production
ROUTE_USE_CACHE=true
PRODUCTS_CACHE_TTL=3600  # 1 hour caching
```

## Testing the Module

### 1. Manual Testing

1. **Navigate to the module:**
   ```
   http://localhost/upMVC/products
   ```

2. **Test CRUD operations:**
   - Create a new product
   - View the product list
   - Edit an existing product
   - Delete a product
   - Test search functionality

3. **Test API endpoints:**
   - Use browser dev tools or Postman
   - Test all HTTP methods (GET, POST, PUT, DELETE)

### 2. Debug Information

Enable debug mode to see detailed information:
```properties
# In .env
ROUTE_DEBUG_OUTPUT=true
```

This will show:
- Route discovery information
- Module loading details
- Environment configuration
- Cache status
- Error details

### 3. Submodule Testing

Test the auto-generated submodule:
```
http://localhost/upMVC/products/example
```

This demonstrates nested module auto-discovery.

## Common Issues & Solutions

### Routes Not Working
```bash
# Check if routes are discovered
# Enable debug output
ROUTE_DEBUG_OUTPUT=true

# Clear cache
ROUTE_USE_CACHE=false

# Check InitModsImproved.php is working
```

### Database Connection Issues
```bash
# Verify .env database settings
DB_HOST=127.0.0.1
DB_NAME=upmvc
DB_USER=root
DB_PASS=your_password

# Test database connection manually
```

### Autoloader Issues
```bash
# Regenerate autoloader
composer dump-autoload

# Check namespace in composer.json
"Products\\": "modules/Products/"
```

## Next Steps

1. **Customize the UI** - Modify views and CSS for your brand
2. **Add Business Logic** - Implement specific product management features
3. **Integrate Authentication** - Use middleware for access control
4. **Add More Submodules** - Create Categories, Reviews, Inventory modules
5. **Implement Caching** - Enable production caching for performance
6. **Add Testing** - Create unit tests for your CRUD operations

## Summary

The enhanced module generator creates a complete, production-ready CRUD module with:

- ✅ Full MVC architecture
- ✅ Auto-discovery integration
- ✅ RESTful API endpoints
- ✅ Responsive Bootstrap UI
- ✅ Form validation
- ✅ Search and pagination
- ✅ Environment awareness
- ✅ Caching support
- ✅ Middleware integration
- ✅ Submodule structure
- ✅ Comprehensive documentation

Your Products module is now ready for production use with minimal additional configuration required!