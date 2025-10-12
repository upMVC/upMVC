# CRUD Module Quick Reference

## Quick Start
```bash
cd tools/modulegenerator-enhanced/
php generate-module.php
```

## Sample CRUD Configuration

### Product Management Module
```
Module name: Products
Module type: crud
Fields:
  - name:VARCHAR(255):text
  - description:TEXT:textarea
  - price:DECIMAL(10,2):number
  - category:VARCHAR(100):text
  - sku:VARCHAR(50):text
  - status:ENUM('active','inactive'):select
Create table: yes
Middleware: yes
Submodules: yes
```

### Blog Posts Module
```
Module name: Posts
Module type: crud
Fields:
  - title:VARCHAR(255):text
  - content:LONGTEXT:textarea
  - excerpt:TEXT:textarea
  - author:VARCHAR(100):text
  - published_date:DATE:date
  - status:ENUM('draft','published','archived'):select
  - featured_image:VARCHAR(255):text
Create table: yes
Middleware: yes
Submodules: no
```

### User Management Module
```
Module name: Users
Module type: crud
Fields:
  - username:VARCHAR(50):text
  - email:VARCHAR(255):email
  - full_name:VARCHAR(255):text
  - phone:VARCHAR(20):text
  - role:ENUM('admin','user','moderator'):select
  - status:ENUM('active','inactive','suspended'):select
  - last_login:DATETIME:datetime-local
Create table: yes
Middleware: yes
Submodules: no
```

### Events Module
```
Module name: Events
Module type: crud
Fields:
  - title:VARCHAR(255):text
  - description:TEXT:textarea
  - start_date:DATETIME:datetime-local
  - end_date:DATETIME:datetime-local
  - location:VARCHAR(255):text
  - max_attendees:INT:number
  - price:DECIMAL(8,2):number
  - status:ENUM('upcoming','ongoing','completed','cancelled'):select
Create table: yes
Middleware: yes
Submodules: yes (for Attendees, Reviews)
```

## Field Types Reference

### SQL Types
- `VARCHAR(n)` - Variable length string
- `TEXT` - Long text content
- `LONGTEXT` - Very long text content
- `INT` - Integer number
- `DECIMAL(m,d)` - Decimal number (m=total digits, d=decimal places)
- `DATE` - Date only (YYYY-MM-DD)
- `DATETIME` - Date and time
- `TIMESTAMP` - Timestamp with timezone
- `ENUM('val1','val2')` - Predefined options

### HTML Input Types
- `text` - Single line text input
- `textarea` - Multi-line text input
- `email` - Email input with validation
- `number` - Numeric input
- `range` - Slider input
- `date` - Date picker
- `datetime-local` - Date and time picker
- `select` - Dropdown selection
- `checkbox` - Checkbox input
- `radio` - Radio button

## Generated Routes

### Web Routes
- `GET /modulename` - List items
- `GET /modulename?action=create` - Create form
- `POST /modulename` (action=create) - Create item
- `GET /modulename?action=edit&id=X` - Edit form
- `POST /modulename` (action=update) - Update item
- `GET /modulename?action=delete&id=X` - Delete item

### API Routes
- `GET /modulename/api` - List all items (JSON)
- `GET /modulename/api?id=X` - Get single item (JSON)
- `POST /modulename/api` - Create item (JSON)
- `PUT /modulename/api` - Update item (JSON)
- `DELETE /modulename/api` - Delete item (JSON)

## Environment Variables

### Required
```properties
# Database
DB_HOST=127.0.0.1
DB_NAME=your_database
DB_USER=your_username
DB_PASS=your_password

# Application
APP_ENV=development
APP_URL=http://localhost
APP_PATH=/upMVC
```

### Optional
```properties
# Route Discovery
ROUTE_SUBMODULE_DISCOVERY=true
ROUTE_USE_CACHE=false
ROUTE_DEBUG_OUTPUT=true

# Module Specific
PRODUCTS_CACHE_TTL=3600
POSTS_PAGINATION_SIZE=10
USERS_REQUIRE_EMAIL_VERIFICATION=true
```

## Post-Generation Checklist

- [ ] Run `composer dump-autoload`
- [ ] Verify database table created
- [ ] Test web interface at `/modulename`
- [ ] Test API endpoints at `/modulename/api`
- [ ] Configure authentication if needed
- [ ] Customize validation rules
- [ ] Style the UI as needed
- [ ] Add business logic
- [ ] Set up production caching
- [ ] Test submodules if created

## Common Customizations

### Add Validation
```php
// In Model.php
private function validateData(array $data): bool
{
    // Custom validation logic
    return true;
}
```

### Add Custom Routes
```php
// In routes/Routes.php
$router->addRoute('/modulename/export', Controller::class, 'export');
```

### Add Middleware
```php
// In Controller.php constructor
$this->addMiddleware('auth');
$this->addMiddleware('admin');
```

### Customize Views
```php
// In views/index.php
// Add custom columns, filters, actions
```

## Troubleshooting

### Routes Not Found
1. Check `ROUTE_SUBMODULE_DISCOVERY=true`
2. Verify `routes/Routes.php` exists
3. Clear cache: `ROUTE_USE_CACHE=false`
4. Run `composer dump-autoload`

### Database Issues
1. Check `.env` database settings
2. Ensure database exists
3. Check table creation SQL output
4. Verify database permissions

### Autoloader Issues
1. Run `composer dump-autoload`
2. Check namespace in `composer.json`
3. Verify PSR-4 mapping

## File Structure
```
modules/YourModule/
├── Controller.php        # CRUD operations + API
├── Model.php            # Data access + validation
├── View.php             # Template rendering
├── routes/Routes.php    # Auto-discovered routes
├── views/
│   ├── layouts/         # Header/footer templates
│   ├── index.php        # List view with pagination
│   ├── create.php       # Creation form
│   └── edit.php         # Edit form
├── assets/
│   ├── css/style.css    # Module styling
│   └── js/script.js     # Interactive features
├── etc/
│   ├── config.php       # Module configuration
│   └── api-docs.md      # API documentation
└── modules/             # Submodules (optional)
```