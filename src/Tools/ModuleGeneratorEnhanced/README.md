# Enhanced Module Generator for upMVC

## 📁 Project Structure

```
modulegenerator-enhanced/
├── ModuleGeneratorEnhanced.php    # Core generator class
├── generate-module.php            # Main CLI script
├── generate.bat                   # Windows batch launcher
├── README.md                      # This file
├── docs/                          # Documentation files
│   ├── INDEX.md                   # Documentation index
│   ├── QUICK-REFERENCE.md         # Quick start guide
│   ├── CRUD-EXAMPLE.md            # CRUD examples
│   ├── COMPARISON.md              # Legacy vs Enhanced
│   ├── NAMESPACE-FIX-VERIFIED.md  # PSR-4 namespace docs
│   ├── NAMING-CONVENTION-FIX.md   # Naming standards
│   └── PROJECT-COMPLETION-SUMMARY.md
└── tests/                         # Test files
    ├── README.md                  # Test documentation
    ├── test-generator.php         # Main test suite
    ├── quick-test.php             # Quick validation
    └── ...                        # Other test files
```

## Overview
This is the next-generation module generator for upMVC that leverages the enhanced features:
- **PSR-4 Compliant** - Generates modules in `src/Modules/` with `App\Modules\` namespace
- **InitModsImproved.php** - Uses automatic route discovery with caching
- **Submodule Support** - Creates modules with nested submodule capabilities  
- **Environment Integration** - Respects .env configuration settings
- **Modern Architecture** - Full PSR-4 compliance with improved error handling

## Differences from Legacy Generator

### Legacy Generator (`tools/modulegenerator/`)
- Uses `InitMods.php` with manual route registration
- Requires manual updates to framework files
- No submodule support
- Basic error handling

### Enhanced Generator (`tools/modulegenerator-enhanced/`)
- Uses `InitModsImproved.php` with automatic route discovery
- No manual framework file updates needed
- Full submodule support with nested structures
- Comprehensive error handling and caching
- Environment-aware configuration

## Features

### Module Types
- **basic** - Simple MVC structure with automatic route discovery
- **crud** - Full CRUD with pagination, search, and API endpoints
- **api** - RESTful API module with JSON responses
- **auth** - Authentication module with middleware integration
- **dashboard** - Admin dashboard with analytics
- **submodule** - Nested submodule within existing modules

### Advanced Features
- Automatic route discovery (no manual registration needed)
- Submodule creation with deep nesting support
- Environment variable integration
- Caching-aware architecture
- Middleware integration ready
- Modern PHP 8.1+ features
- PSR-4 autoloading compliance

## Usage

### Command Line
```bash
php generate-module.php
```

### Web Interface
Access via browser:
```
http://localhost/upMVC/tools/modulegenerator-enhanced/
```

### Configuration
The generator respects these environment variables:
- `ROUTE_USE_CACHE` - Enable route caching
- `ROUTE_SUBMODULE_DISCOVERY` - Enable submodule discovery
- `ROUTE_DEBUG_OUTPUT` - Enable debug output
- `APP_ENV` - Application environment (development/production)

## Module Structure
```
modules/
├── YourModule/
│   ├── Controller.php
│   ├── Model.php  
│   ├── View.php
│   ├── routes/
│   │   └── Routes.php        # Auto-discovered by InitModsImproved
│   ├── views/
│   │   ├── layouts/
│   │   └── *.php
│   ├── assets/
│   │   ├── style.css
│   │   └── script.js
│   ├── etc/
│   └── modules/             # Submodules (if enabled)
│       └── SubModule/
│           └── routes/
│               └── Routes.php  # Auto-discovered recursively
```

## Integration with Enhanced upMVC

### Automatic Route Discovery
- Routes are automatically discovered by `InitModsImproved.php`
- No need to manually update `InitMods.php` or other framework files
- Supports deep nested submodule discovery

### Environment Awareness
- Respects development/production environment settings
- Automatic caching in production environments
- Debug output controlled by environment variables

### Middleware Ready
- Generated modules are ready for middleware integration
- Authentication modules integrate with the middleware system
- No separate login modules needed (uses middleware instead)

## Migration from Legacy Generator

### For New Projects
- Use the enhanced generator directly
- All new modules will be auto-discovered

### For Existing Projects
1. Keep existing modules generated with legacy generator
2. Use enhanced generator for new modules
3. Both systems can coexist
4. Consider migrating critical modules when time permits

## Examples

### Basic Module
```bash
# Creates: modules/Blog/ with auto-discovery
php generate-module.php
# Select: basic
# Name: Blog
```

### CRUD Module with Submodule
```bash
# Creates: modules/Products/ with modules/Products/modules/Categories/
php generate-module.php  
# Select: crud
# Name: Products
# Enable submodules: yes
```

### API Module
```bash
# Creates: modules/ApiV1/ with RESTful endpoints
php generate-module.php
# Select: api
# Name: ApiV1
```

### Complete CRUD Module Example
Here's a step-by-step example creating a complete Products CRUD module:

```bash
# 1. Navigate to the enhanced generator
cd tools/modulegenerator-enhanced/

# 2. Run the generator
php generate-module.php

# 3. Follow the interactive prompts:
📝 Enter module name: Products
📋 Select module type: crud
📝 Define fields for your CRUD module:
🔹 Field (name:sql_type:html_type): name:VARCHAR(255):text
🔹 Field (name:sql_type:html_type): description:TEXT:textarea
🔹 Field (name:sql_type:html_type): price:DECIMAL(10,2):number
🔹 Field (name:sql_type:html_type): category_id:INT:select
🔹 Field (name:sql_type:html_type): status:ENUM('active','inactive'):select
🔹 Field (name:sql_type:html_type): [Enter to finish]
🗄️ Create database table automatically? [Y/n]: y
🔒 Enable middleware integration? [Y/n]: y
📦 Create submodule structure? [y/N]: y

# 4. Update autoloader
composer dump-autoload
```

**Generated Structure:**
```
modules/Products/
├── Controller.php           # Full CRUD controller with API endpoints
├── Model.php               # Enhanced model with validation & caching
├── View.php                # Bootstrap-enabled view renderer
├── routes/
│   └── Routes.php          # Auto-discovered CRUD routes
├── views/
│   ├── layouts/
│   │   ├── header.php      # Bootstrap header with navigation
│   │   └── footer.php      # Enhanced footer
│   ├── index.php           # Product listing with pagination
│   ├── create.php          # Product creation form
│   └── edit.php            # Product editing form
├── assets/
│   ├── css/style.css       # Product-specific styling
│   └── js/script.js        # CRUD interactions & AJAX
├── etc/
│   ├── config.php          # Module configuration
│   └── api-docs.md         # Complete API documentation
└── modules/                # Submodule container
    └── Example/
        ├── Controller.php
        └── routes/Routes.php
```

**Auto-Generated Routes:**
- `GET /products` - List all products with pagination
- `GET /products?action=create` - Show create form
- `POST /products` (action=create) - Create new product
- `GET /products?action=edit&id=1` - Show edit form
- `POST /products` (action=update) - Update product
- `GET /products?action=delete&id=1` - Delete product
- `GET /products/api` - JSON API endpoint
- `POST /products/api` - Create via API
- `PUT /products/api` - Update via API
- `DELETE /products/api` - Delete via API

**Generated Database Table:**
```sql
CREATE TABLE IF NOT EXISTS `products` (
  id INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `category_id` INT NOT NULL,
  `status` ENUM('active','inactive') NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Access Your Module:**
- **Web Interface:** `http://localhost/upMVC/products`
- **API Endpoint:** `http://localhost/upMVC/products/api`
- **Submodule:** `http://localhost/upMVC/products/example`

**Features Included:**
- ✅ Full CRUD operations (Create, Read, Update, Delete)
- ✅ Responsive Bootstrap UI with pagination
- ✅ Form validation and error handling
- ✅ RESTful API endpoints with JSON responses
- ✅ Search and filtering capabilities
- ✅ Flash messages for user feedback
- ✅ Middleware-ready authentication hooks
- ✅ Auto-discovery (no manual route registration)
- ✅ Production-ready caching integration
- ✅ Environment-aware debugging
- ✅ Submodule structure for extensibility

## Files Generated

### Core Files (All Module Types)
- `Controller.php` - Main controller with auto-routed methods
- `Model.php` - Data access layer with base functionality  
- `View.php` - View renderer with template system
- `routes/Routes.php` - Route definitions (auto-discovered)

### Additional Files (Type-Dependent)
- `views/` - HTML templates with Bootstrap integration
- `assets/` - CSS/JS files for frontend
- `etc/` - Configuration and documentation files
- `modules/` - Submodule container (if enabled)

## Advanced Usage

### Creating Submodules
The enhanced generator can create submodules within existing modules:

```bash
php generate-module.php --parent=Products --type=submodule --name=Categories
```

This creates: `modules/Products/modules/Categories/` with automatic discovery.

### Environment Configuration
Create `.env` entries for module-specific settings:
```properties
# Module-specific configuration
PRODUCTS_API_ENABLED=true
PRODUCTS_CACHE_TTL=3600
BLOG_PAGINATION_SIZE=10
```

## Troubleshooting

### Routes Not Found
1. Check `ROUTE_SUBMODULE_DISCOVERY=true` in `.env`
2. Verify `Routes.php` exists in `routes/` folder
3. Clear cache: delete cache files or set `ROUTE_USE_CACHE=false`

### Debug Information
Enable debug output to see route discovery:
```properties
ROUTE_DEBUG_OUTPUT=true
```

### Cache Issues
In development, disable caching:
```properties
ROUTE_USE_CACHE=false
```

## 📚 Documentation

### Quick Links
- **[Quick Reference](docs/QUICK-REFERENCE.md)** - Fast commands and examples
- **[CRUD Examples](docs/CRUD-EXAMPLE.md)** - Complete CRUD module tutorials
- **[Comparison Guide](docs/COMPARISON.md)** - Legacy vs Enhanced differences
- **[All Documentation](docs/INDEX.md)** - Complete documentation index

### Testing
See [tests/README.md](tests/README.md) for information about running tests and validation.

## Support

For issues or questions:
1. Check this README and [documentation](docs/INDEX.md)
2. Review generated module structure
3. Run tests in [tests/](tests/) folder
4. Enable debug output for troubleshooting
5. Compare with legacy generator output if needed