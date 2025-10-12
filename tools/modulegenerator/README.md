# Enhanced Module Generator for upMVC

A powerful command-line tool for generating complete modules in the upMVC framework.

## Features

- **Interactive CLI**: User-friendly command-line interface with prompts
- **Multiple Module Types**: Support for basic, CRUD, API, auth, and dashboard modules
- **Auto-generation**: Creates complete MVC structure with routes, views, and assets
- **Database Integration**: Automatic table creation for CRUD modules
- **Framework Integration**: Updates composer.json and InitMods.php automatically
- **Modern UI**: Bootstrap-based responsive templates
- **API Documentation**: Auto-generated API docs for API modules

## Usage

### Command Line

```bash
cd tools/modulegenerator
php generate-module.php
```

### Module Types

1. **Basic Module**: Simple MVC structure with basic views
2. **CRUD Module**: Full Create, Read, Update, Delete functionality with database
3. **API Module**: RESTful API endpoints with JSON responses
4. **Auth Module**: Authentication module with login/logout
5. **Dashboard Module**: Admin dashboard with statistics and management

### Example: Creating a CRUD Module

```bash
$ php generate-module.php

╔══════════════════════════════════════════════════════════════════╗
║                     upMVC Module Generator                       ║
║                     Enhanced Version 2.0                        ║
╚══════════════════════════════════════════════════════════════════╝

Enter module name (e.g., Blog, Product, User): Product

Available module types:
  [basic] Basic module with simple MVC structure
  [crud] Full CRUD module with database operations
  [api] API-only module with JSON responses
  [auth] Authentication module with login/logout
  [dashboard] Dashboard module with admin interface

Select module type (basic/crud/api/auth/dashboard): crud

Define fields for your CRUD module:
Enter field definitions (press Enter on empty field name to finish)
Format: fieldname:type:html_input_type (e.g., name:VARCHAR(100):text)

Field (name:sql_type:html_type): name:VARCHAR(255):text
Added field: name (VARCHAR(255)) -> text

Field (name:sql_type:html_type): price:DECIMAL(10,2):number
Added field: price (DECIMAL(10,2)) -> number

Field (name:sql_type:html_type): description:TEXT:textarea
Added field: description (TEXT) -> textarea

Field (name:sql_type:html_type): 

Create database table automatically? (y/n): y

Generating module 'Product'...
Created directory: /path/to/modules/Product
Created directory: /path/to/modules/Product/routes
...
Module generation completed successfully!

╔══════════════════════════════════════════════════════════════════╗
║                     SUCCESS!                                    ║
╚══════════════════════════════════════════════════════════════════╝

Module 'Product' has been generated successfully!

Next steps:
1. Run 'composer dump-autoload' to update autoloader
2. Check the generated files in modules/Product/
3. Run the SQL commands to create the database table
4. Access your module at: http://localhost/products

Happy coding!
```

## Generated Structure

When you generate a module, the following structure is created:

```
modules/YourModule/
├── Controller.php          # Main controller with all actions
├── Model.php              # Model with database operations
├── View.php               # View handler for rendering templates
├── routes/
│   └── Routes.php         # Route definitions
├── views/
│   ├── layouts/
│   │   ├── header.php     # Common header
│   │   └── footer.php     # Common footer
│   ├── index.php          # List view (CRUD)
│   ├── create.php         # Create form (CRUD)
│   └── edit.php           # Edit form (CRUD)
├── assets/
│   ├── style.css          # Custom CSS
│   └── script.js          # Custom JavaScript
└── etc/
    └── api-docs.md        # API documentation (API modules)
```

## Field Types

For CRUD modules, you can define fields with different types:

### SQL Types
- `VARCHAR(n)` - Variable length string
- `TEXT` - Long text
- `INT` - Integer
- `DECIMAL(p,s)` - Decimal number
- `DATE` - Date
- `DATETIME` - Date and time
- `ENUM('val1','val2')` - Enumerated values
- `TINYINT(1)` - Boolean (0/1)

### HTML Input Types
- `text` - Text input
- `email` - Email input
- `number` - Number input
- `password` - Password input
- `textarea` - Multi-line text
- `select` - Dropdown
- `radio` - Radio buttons
- `checkbox` - Checkbox
- `date` - Date picker
- `datetime-local` - Date and time picker

## Features by Module Type

### Basic Module
- Simple controller with display and about methods
- Basic view templates
- Responsive layout with Bootstrap
- Custom CSS and JavaScript files

### CRUD Module
- Full CRUD operations (Create, Read, Update, Delete)
- Pagination support
- Search functionality
- Flash messaging system
- Form validation
- API endpoints for AJAX operations
- Responsive data tables
- Confirmation dialogs

### API Module
- RESTful endpoints (GET, POST, PUT, DELETE)
- JSON responses
- Error handling
- API documentation
- CORS support

### Auth Module
- User registration and login
- Password hashing
- Session management
- Protected routes
- Password reset functionality

### Dashboard Module
- Statistics and analytics
- Admin interface
- Charts and graphs
- User management
- System settings

## Advanced Features

### Flash Messaging
The generated modules include a flash messaging system for user feedback:

```php
$_SESSION['flash_message'] = 'Record created successfully!';
$_SESSION['flash_type'] = 'success'; // success, error, warning, info
```

### AJAX Support
CRUD modules include API endpoints for AJAX operations:

```javascript
// Get all records
fetch('/api/products')
    .then(response => response.json())
    .then(data => console.log(data));

// Create new record
fetch('/api/products', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({name: 'New Product', price: 99.99})
})
.then(response => response.json())
.then(data => console.log(data));
```

### Validation
Built-in validation for form data:

```php
private function validateData(array $data): bool
{
    $requiredFields = ['name', 'price'];
    
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            return false;
        }
    }
    
    return true;
}
```

## Customization

After generating a module, you can customize:

1. **Views**: Edit templates in `views/` directory
2. **Styles**: Modify `assets/style.css`
3. **JavaScript**: Update `assets/script.js`
4. **Validation**: Add custom validation in Model
5. **Routes**: Add more routes in `routes/Routes.php`
6. **Controller**: Add new actions in Controller

## Requirements

- PHP 8.1+
- upMVC framework
- MySQL database (for CRUD modules)
- Composer for autoloading

## Troubleshooting

### Common Issues

1. **Module not found**: Run `composer dump-autoload` after generation
2. **Database errors**: Check database configuration in `etc/ConfigDatabase.php`
3. **Permission errors**: Ensure write permissions on `modules/` directory
4. **Route conflicts**: Check for duplicate routes in existing modules

### Getting Help

1. Check the generated `api-docs.md` file for API modules
2. Review the generated code for examples
3. Examine existing modules for patterns
4. Check upMVC documentation

## Contributing

Feel free to contribute improvements to this module generator:

1. Add new module types
2. Improve templates
3. Add more field types
4. Enhance validation
5. Add tests

## License

This tool is part of the upMVC framework and follows the same MIT license.