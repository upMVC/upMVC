# Dashboard Module Documentation

## Overview
The Dashboard Module is a comprehensive admin interface with user management, theme switching, and settings management capabilities. It demonstrates the proper use of the MVC pattern, database integration, and global view data management.

## Quick Start

### 1. Database Setup
```sql
-- Run the schema.sql file or use the following:
CREATE DATABASE your_database;
USE your_database;

-- Import schema
source modules/dashboard/sql/schema.sql
```

### 2. Configuration
```php
// etc/ConfigDatabase.php
private static $config = [
    'db' => [
        'host' => '127.0.0.1',
        'name' => 'your_database',
        'user' => 'your_username',
        'pass' => 'your_password',
    ],
];
```

### 3. Initial Login
- URL: `your-domain/dashboard`
- Default credentials:
  - Email: admin@example.com
  - Password: admin123

### 4. First Steps
1. Change admin password
2. Configure site settings
3. Add additional users
4. Customize theme

## Features
- User Authentication & Management
- Dark/Light Theme Support
- Settings Management
- Responsive Design with Tailwind CSS
- MariaDB Integration

## Architecture

### Database Structure
```sql
-- Users Table
dashboard_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    password VARCHAR(255),
    role ENUM('admin', 'user'),
    last_login DATETIME
)

-- Settings Table
dashboard_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(255) UNIQUE,
    setting_value TEXT
)
```

### Component Structure
```
modules/dashboard/
├── Controller.php      # Main controller logic
├── Model.php          # Database interactions
├── View.php           # View handling
├── routes/            # Route definitions
├── sql/              # Database schema
└── templates/         # View templates
    ├── layout/       # Shared layout files
    ├── dashboard.php # Main dashboard view
    ├── users.php    # User management view
    └── settings.php # Settings management view
```

## How It Works

### 1. Settings Management
Settings are stored in the database and managed through a three-layer system:

```php
// Model Layer - Fetching settings
public function getSettings() {
    $stmt = $this->db->prepare("SELECT setting_key, setting_value FROM dashboard_settings");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
}

// Controller Layer - Loading settings
public function __construct() {
    $this->model = new Model();
    $this->view = new View();
    $settings = $this->model->getSettings();
    $this->view->addGlobal('settings', $settings);
}

// View Layer - Using settings
<html lang="en" class="<?php echo ($settings['theme'] ?? 'light') === 'dark' ? 'dark' : ''; ?>">
```

### 2. Theme System
The theme system uses Tailwind CSS's dark mode with class strategy and supports real-time switching:

```html
<!-- Base element with dark mode class -->
<html class="dark">

<!-- Component with dark mode variants -->
<div class="bg-white dark:bg-gray-800">
    <h1 class="text-gray-900 dark:text-white">
        Dashboard
    </h1>
</div>

<!-- Theme switcher -->
<script>
// Initialize theme
const html = document.documentElement;
const themeSelect = document.getElementById('theme');

// Function to update theme
function updateTheme(theme) {
    if (theme === 'dark') {
        html.classList.add('dark');
    } else {
        html.classList.remove('dark');
    }
    
    // Save theme preference
    fetch(`${BASE_URL}/dashboard/settings`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `theme=${theme}`
    });
}

// Handle theme changes
themeSelect.addEventListener('change', function() {
    updateTheme(this.value);
});

// Initialize theme on page load
updateTheme(themeSelect.value);
</script>
```

The theme system provides:
- Instant theme switching without page reload
- Persistent theme preference across sessions
- Consistent styling with Tailwind dark mode
- Smooth transitions between themes

### 3. Global View Data
The module uses BaseView's globals system for sharing data across views:

```php
// Adding global data in Controller
$this->view->addGlobal('settings', [
    'theme' => 'dark',
    'site_name' => 'Dashboard'
]);

// Accessing in any template
$currentTheme = $settings['theme'] ?? 'light';
$siteName = $settings['site_name'] ?? 'Dashboard';

// Using with dark mode classes
<div class="<?php echo $currentTheme === 'dark' ? 'bg-gray-800 text-white' : 'bg-white text-gray-900'; ?>">
    <h1><?php echo $siteName; ?></h1>
</div>

// Updating settings
public function updateSettings($key, $value) {
    // 1. Update database
    $this->model->updateSetting($key, $value);
    
    // 2. Update globals
    $settings = $this->view->getGlobal('settings') ?? [];
    $settings[$key] = $value;
    $this->view->addGlobal('settings', $settings);
}
```

The globals system provides:
- Centralized settings management
- Consistent theme across all pages
- Easy access to configuration in templates
- Real-time updates without page reload

## Extending the Module

### 1. Adding New Settings
```php
// 1. Add to schema.sql
INSERT INTO dashboard_settings (setting_key, setting_value) 
VALUES ('new_setting', 'default_value');

// 2. Use in templates
<?php echo $settings['new_setting'] ?? 'fallback'; ?>
```

### 2. Creating New Views
```php
// 1. Add template file (templates/new-view.php)
<div class="bg-white dark:bg-gray-800">
    <!-- Your content -->
</div>

// 2. Add controller method
public function newView() {
    $this->view->render('new-view', [
        'title' => 'New View',
        'data' => $this->model->getData()
    ]);
}

// 3. Add route (routes/Routes.php)
'/dashboard/new-view' => [
    'controller' => 'Dashboard\Controller',
    'action' => 'newView'
]
```

### 3. Adding Custom Functionality
```php
// 1. Extend the Model
public function customFeature() {
    // Add your custom database operations
}

// 2. Add Controller Method
public function handleCustomFeature() {
    if (!$this->isAuthenticated()) {
        return $this->redirectToLogin();
    }
    
    $result = $this->model->customFeature();
    $this->view->render('custom-template', [
        'result' => $result
    ]);
}

// 3. Create Template
<div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
        Custom Feature
    </h2>
    <!-- Your custom content -->
</div>

// 4. Add to Navigation
<a href="<?php echo BASE_URL ?>/dashboard/custom-feature" 
   class="flex items-center px-6 py-3 hover:bg-gray-700">
    <i class="fas fa-star mr-3"></i>
    Custom Feature
</a>
```

### 4. Adding API Endpoints
```php
// 1. Add to Routes.php
'/dashboard/api/custom' => [
    'controller' => 'Dashboard\Controller',
    'action' => 'apiCustom',
    'method' => ['GET', 'POST']
]

// 2. Add Controller Method
public function apiCustom() {
    header('Content-Type: application/json');
    
    if (!$this->isAuthenticated()) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }
    
    $result = $this->model->customFeature();
    echo json_encode(['data' => $result]);
}
```

## Best Practices

1. **Settings Management**
   - Always use the settings table for configuration
   - Provide default values as fallback
   - Use type-appropriate columns in database

2. **Theme Handling**
   - Use Tailwind's dark mode classes consistently
   - Test both light and dark modes
   - Maintain color contrast ratios

3. **Security**
   - Always hash passwords
   - Validate user roles
   - Sanitize input data
   - Use prepared statements

4. **Performance**
   - Cache settings where appropriate
   - Use database indexes
   - Optimize queries

## Troubleshooting

1. **Theme Not Updating**
   - Check database connection
   - Verify settings table exists
   - Confirm theme value in dashboard_settings
   - Check browser console for errors

2. **User Management Issues**
   - Verify database permissions
   - Check role assignments
   - Validate password hashing

3. **Database Errors**
   - Check MariaDB connection
   - Verify table structure
   - Review SQL syntax

## Contributing
1. Follow the existing code structure
2. Maintain dark mode support
3. Add appropriate documentation
4. Test thoroughly
5. Submit pull request

## License
MIT License - See LICENSE file for details
