# upMVC Module Generation Tools

This directory contains two powerful tools for generating modules in upMVC:
1. Module Generator - Creates basic MVC, API, and React modules
2. CRUD Generator - Creates full CRUD modules with database integration

## 1. Module Generator

Located in `tools/createmodule/`, this tool creates different types of modules with proper MVC structure.

### Usage

```bash
php tools/createmodule/create-module.php
```

### Interactive Prompts
1. Enter module name (e.g., Blog)
2. Select module type:
   - 1: basic (Standard MVC module)
   - 2: api (REST API module)
   - 3: react (React integration module)

### Example

```bash
$ php tools/createmodule/create-module.php

=== upMVC Module Generator ===

Enter module name (e.g., Blog): Blog

Available module types:
1. basic - Standard MVC module
2. api - REST API module
3. react - React integration module

Select module type (1-3, default: 1): 1

Generating basic module 'Blog'...
✅ Module generated successfully!

Next steps:
1. Run 'composer dump-autoload' to update autoloader
2. Check the generated files in modules/Blog/
3. Add your custom logic to the Controller, Model, and View
4. Access your module at: http://your-domain/Blog
```

### Generated Structure
```
modules/YourModule/
├── Controller.php
├── Model.php
├── View.php
├── routes/
│   └── Routes.php
└── templates/
    └── index.php
```

## 2. CRUD Generator

Located in `tools/crudgenerator/`, this tool creates full CRUD modules with database integration and form handling.

### Usage

Create a PHP script with your module configuration:

```php
<?php
require_once __DIR__ . '/../../vendor/autoload.php';
use Tools\CrudGenerator\CrudModuleGenerator;

// Define module name
$moduleName = 'Products';

// Define fields with SQL and HTML types
$fields = [
    [
        'name' => 'name',
        'sql_type' => 'VARCHAR(100)',
        'html_type' => 'text'
    ],
    [
        'name' => 'price',
        'sql_type' => 'DECIMAL(10,2)',
        'html_type' => 'number',
        'step' => '0.01',
        'min' => '0'
    ],
    [
        'name' => 'category',
        'sql_type' => 'VARCHAR(50)',
        'html_type' => 'select',
        'options' => [
            ['value' => 'electronics', 'label' => 'Electronics'],
            ['value' => 'clothing', 'label' => 'Clothing']
        ]
    ]
];

// Generate module
$generator = new CrudModuleGenerator($moduleName, $fields);
$generator->generate();
$generator->createTable();
```

### Field Types

1. **Basic Types**
```php
// Text input
['name' => 'title', 'sql_type' => 'VARCHAR(100)', 'html_type' => 'text']

// Number input
['name' => 'quantity', 'sql_type' => 'INT', 'html_type' => 'number']

// Email input
['name' => 'email', 'sql_type' => 'VARCHAR(255)', 'html_type' => 'email']

// Textarea
['name' => 'description', 'sql_type' => 'TEXT', 'html_type' => 'textarea']
```

2. **Select Dropdown**
```php
[
    'name' => 'status',
    'sql_type' => 'VARCHAR(20)',
    'html_type' => 'select',
    'options' => [
        ['value' => 'active', 'label' => 'Active'],
        ['value' => 'inactive', 'label' => 'Inactive']
    ]
]
```

3. **Radio Buttons**
```php
[
    'name' => 'type',
    'sql_type' => 'VARCHAR(10)',
    'html_type' => 'radio',
    'options' => [
        ['value' => 'new', 'label' => 'New'],
        ['value' => 'used', 'label' => 'Used']
    ]
]
```

### Generated Features

1. **Database Integration**
   - Automatic table creation
   - Prepared statements
   - SQL injection protection

2. **CRUD Operations**
   - Create new records
   - Read/List records
   - Update existing records
   - Delete records

3. **Form Handling**
   - Input validation
   - Error handling
   - CSRF protection
   - File uploads (if specified)

4. **Views**
   - List view with pagination
   - Create form
   - Edit form
   - Delete confirmation

### Generated Structure
```
modules/YourModule/
├── Controller.php
├── Model.php
├── View.php
├── routes/
│   └── Routes.php
├── templates/
│   ├── list.php
│   ├── create.php
│   ├── edit.php
│   └── layout/
│       ├── header.php
│       └── footer.php
└── sql/
    └── schema.sql
```

## Best Practices

1. **Module Names**
   - Use PascalCase (e.g., BlogPosts)
   - Keep names descriptive
   - Avoid special characters

2. **Field Configuration**
   - Use appropriate SQL types
   - Set proper field lengths
   - Add validation rules
   - Use meaningful names

3. **After Generation**
   - Review generated code
   - Add custom validation
   - Customize templates
   - Add indexes to database
   - Update routes if needed

## Troubleshooting

1. **Permission Issues**
```bash
chmod +x tools/createmodule/create-module.php
chmod +x tools/crudgenerator/create-module.php
```

2. **Database Connection**
   - Verify database credentials
   - Check table creation rights
   - Ensure database exists

3. **Autoloader Issues**
```bash
composer dump-autoload
```

## Examples

### 1. Blog Module
```php
$fields = [
    ['name' => 'title', 'sql_type' => 'VARCHAR(200)', 'html_type' => 'text'],
    ['name' => 'content', 'sql_type' => 'TEXT', 'html_type' => 'textarea'],
    ['name' => 'author', 'sql_type' => 'VARCHAR(100)', 'html_type' => 'text'],
    [
        'name' => 'status',
        'sql_type' => 'VARCHAR(20)',
        'html_type' => 'select',
        'options' => [
            ['value' => 'draft', 'label' => 'Draft'],
            ['value' => 'published', 'label' => 'Published']
        ]
    ]
];
```

### 2. Product Catalog
```php
$<thinking>
I have gathered enough information about both tools. Now I will create a comprehensive README.md for the tools directory that explains:

1. Overview of both tools
2. How to use each tool
3. CLI commands and prompts
4. Examples of usage
5. How to extend or customize
6. Any prerequisites or dependencies
</thinking>

<create_file>
<path>tools/README.md</path>
<content># Tools Directory Documentation

This directory contains two powerful tools for generating modules in the upMVC framework:

1. **General Module Generator**
2. **CRUD Module Generator**

---

## 1. General Module Generator

### Overview
The General Module Generator allows you to quickly scaffold a new module with a standard MVC structure or specialized types like API or React modules.

### How to Use
Run the script from the command line:

```bash
php tools/createmodule/create-module.php
```

You will be prompted to enter:

- **Module Name**: The name of your new module (e.g., Blog)
- **Module Type**: Choose from:
  - `basic` - Standard MVC module
  - `api` - REST API module
  - `react` - React integration module

### Example Session

```
=== upMVC Module Generator ===

Enter module name (e.g., Blog): Blog

Available module types:
1. basic - Standard MVC module
2. api - REST API module
3. react - React integration module

Select module type (1-3, default: 1): 1

Generating basic module 'Blog'...

✅ Module generated successfully!

Next steps:
1. Run 'composer dump-autoload' to update autoloader
2. Check the generated files in modules/Blog/
3. Add your custom logic to the Controller, Model, and View
4. Access your module at: http://your-domain/Blog
```

### Output
The tool generates the module directory with Controller, Model, View, routes, templates, and other necessary files.

---

## 2. CRUD Module Generator

### Overview
The CRUD Module Generator helps you create a fully functional CRUD module with database integration, including form fields with various input types.

### How to Use
Edit the `tools/crudgenerator/create-module.php` file to specify:

- **Module Name**: The name of the CRUD module
- **Fields**: An array defining each field's name, SQL type, HTML input type, and options for select/radio inputs

Then run:

```bash
php tools/crudgenerator/create-module.php
```

### Example Fields Definition

```php
$fields = [
    ['name' => 'name', 'sql_type' => 'VARCHAR(100)', 'html_type' => 'text'],
    ['name' => 'price', 'sql_type' => 'DECIMAL(10,2)', 'html_type' => 'number', 'step' => '0.01', 'min' => '0'],
    ['name' => 'description', 'sql_type' => 'TEXT', 'html_type' => 'textarea'],
    [
        'name' => 'category',
        'sql_type' => 'VARCHAR(50)',
        'html_type' => 'select',
        'options' => [
            ['value' => 'electronics', 'label' => 'Electronics'],
            ['value' => 'clothing', 'label' => 'Clothing'],
            ['value' => 'books', 'label' => 'Books'],
            ['value' => 'food', 'label' => 'Food & Beverages']
        ]
    ],
    [
        'name' => 'availability',
        'sql_type' => 'VARCHAR(20)',
        'html_type' => 'radio',
        'options' => [
            ['value' => 'in_stock', 'label' => 'In Stock'],
            ['value' => 'out_of_stock', 'label' => 'Out of Stock'],
            ['value' => 'pre_order', 'label' => 'Pre-order']
        ]
    ],
    ['name' => 'email', 'sql_type' => 'VARCHAR(255)', 'html_type' => 'email'],
];
```

### Output
The tool generates the module files with CRUD operations and creates the corresponding database table.

---

## Extending and Customizing

- Both tools generate standard MVC module structures that you can customize.
- Add your business logic in the Controller and Model.
- Modify templates for custom UI.
- Extend the field definitions in CRUD generator for additional input types.

---

## Prerequisites

- PHP 8.1 or higher
- Composer dependencies installed (`composer install`)
- Database configured and accessible

---

## Common Use Cases

### 1. Blog Module with Comments
```php
$fields = [
    ['name' => 'title', 'sql_type' => 'VARCHAR(255)', 'html_type' => 'text'],
    ['name' => 'content', 'sql_type' => 'TEXT', 'html_type' => 'textarea'],
    [
        'name' => 'status',
        'sql_type' => 'VARCHAR(20)',
        'html_type' => 'select',
        'options' => [
            ['value' => 'draft', 'label' => 'Draft'],
            ['value' => 'published', 'label' => 'Published'],
            ['value' => 'archived', 'label' => 'Archived']
        ]
    ],
    ['name' => 'publish_date', 'sql_type' => 'DATETIME', 'html_type' => 'datetime-local']
];
```

### 2. User Profile Module
```php
$fields = [
    ['name' => 'username', 'sql_type' => 'VARCHAR(50)', 'html_type' => 'text'],
    ['name' => 'email', 'sql_type' => 'VARCHAR(255)', 'html_type' => 'email'],
    [
        'name' => 'role',
        'sql_type' => 'VARCHAR(20)',
        'html_type' => 'radio',
        'options' => [
            ['value' => 'user', 'label' => 'Regular User'],
            ['value' => 'admin', 'label' => 'Administrator'],
            ['value' => 'editor', 'label' => 'Content Editor']
        ]
    ],
    ['name' => 'bio', 'sql_type' => 'TEXT', 'html_type' => 'textarea']
];
```

## Troubleshooting Guide

### 1. Module Generation Issues
```bash
# If permission denied
chmod +x tools/createmodule/create-module.php
chmod +x tools/crudgenerator/create-module.php

# If autoloader not found
composer dump-autoload

# If module directory already exists
rm -rf modules/YourModule
```

### 2. Database Issues
```php
// Check database connection in etc/ConfigDatabase.php
private static $config = [
    'db' => [
        'host' => '127.0.0.1',  // Verify host
        'name' => 'your_db',    // Verify database exists
        'user' => 'your_user',  // Check credentials
        'pass' => 'your_pass',
    ],
];

// Verify table creation rights
GRANT CREATE, ALTER, DROP ON your_db.* TO 'your_user'@'localhost';
```

### 3. Common Errors

1. **Module Name Issues**
   - Use PascalCase (e.g., BlogPosts)
   - Avoid special characters
   - Don't use PHP reserved words

2. **Field Type Issues**
   - Match SQL types with data requirements
   - Use appropriate HTML input types
   - Verify select/radio options format

3. **Route Conflicts**
   - Check for duplicate routes
   - Verify route parameters
   - Update route prefix if needed

## Best Practices

### 1. Module Organization
```
modules/YourModule/
├── Controller.php      # Business logic
├── Model.php          # Database operations
├── View.php           # View handling
├── routes/            # Route definitions
├── templates/         # View templates
└── sql/              # Database schema
```

### 2. Field Naming
```php
// Good examples
['name' => 'first_name', 'sql_type' => 'VARCHAR(50)']
['name' => 'email_address', 'sql_type' => 'VARCHAR(255)']

// Avoid
['name' => 'fn', 'sql_type' => 'VARCHAR(50)']      // Too short
['name' => 'userEmailAddress', 'sql_type' => '...'] // Use underscores
```

### 3. Security Considerations
- Always validate input
- Use prepared statements
- Implement CSRF protection
- Sanitize output

## Support and Resources

### Documentation
- [upMVC Documentation](https://upmvc.com/docs)
- [Module Development Guide](https://upmvc.com/docs/modules)
- [Database Integration](https://upmvc.com/docs/database)

### Getting Help
- GitHub Issues: Report bugs and feature requests
- Community Forum: Ask questions and share solutions
- Discord Channel: Real-time community support

## License
MIT License - See LICENSE file for details
