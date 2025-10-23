# 🔥 Integration Guide: upMVC + PHP CRUD API Generator

## The Power Combo: Full-Stack PHP Solution

Combine **upMVC** (MVC framework) with **PHP CRUD API Generator** (REST API) to create a complete full-stack application with:

- ✅ Server-side rendering (upMVC)
- ✅ RESTful API endpoints (PHP CRUD API Generator)
- ✅ Shared database and authentication
- ✅ Modern frontend integration (React/Vue with Islands)
- ✅ Mobile app backend
- ✅ Admin panel + API in one system

---

## 🎯 Architecture Overview

```
/myproject (upMVC Root)
├── index.php                  → upMVC entry point
├── etc/
│   └── .env                   → Shared database config
├── modules/
│   ├── admin/                 → User management UI
│   ├── auth/                  → Authentication system
│   ├── dashboard/             → Admin dashboard
│   └── react/                 → React apps using API
└── api/                       → PHP CRUD API Generator subfolder
    ├── index.php              → API entry point
    ├── config/
    │   ├── db.php             → Points to same DB!
    │   └── api.php            → JWT, RBAC, rate limiting
    └── dashboard.html         → API monitoring
```

---

## 📦 Installation Steps

### Step 1: Install upMVC

```bash
# Create upMVC project
composer create-project bitshost/upmvc myproject
cd myproject

# Configure etc/.env
# - SITE_PATH=/myproject
# - DOMAIN_NAME=http://localhost
# - Database credentials
```

### Step 2: Install PHP CRUD API Generator in `/api` Subfolder

```bash
# Create api directory
mkdir api
cd api

# Install as library
composer require bitshost/php-crud-api-generator

# Copy essential files
copy vendor/bitshost/php-crud-api-generator/public/index.php index.php
copy vendor/bitshost/php-crud-api-generator/dashboard.html dashboard.html
copy vendor/bitshost/php-crud-api-generator/health.php health.php
```

### Step 3: Configure API to Use Same Database

Edit `api/index.php` to point to vendor configs:

```php
// api/index.php (lines ~51)
$dbConfig = require __DIR__ . '/vendor/bitshost/php-crud-api-generator/config/db.php';
$apiConfig = require __DIR__ . '/vendor/bitshost/php-crud-api-generator/config/api.php';
```

Edit `vendor/bitshost/php-crud-api-generator/config/db.php`:

```php
<?php
// Use SAME credentials as upMVC etc/.env
return [
    'host' => '127.0.0.1',
    'dbname' => 'myproject_db',  // Same as upMVC
    'user' => 'root',             // Same as upMVC
    'pass' => '',                 // Same as upMVC
    'charset' => 'utf8mb4'
];
```

### Step 4: Configure JWT Authentication (Optional but Recommended)

Edit `vendor/bitshost/php-crud-api-generator/config/api.php`:

```php
<?php
return [
    'auth_enabled' => true,
    'auth_method' => 'jwt',
    
    // JWT settings
    'jwt_secret' => 'your-secret-key-min-32-chars-long',
    'jwt_issuer' => 'myproject.local',
    'jwt_audience' => 'myproject.local',
    'jwt_expiration' => 3600,
    
    // Basic users for JWT login (same as upMVC users)
    'basic_users' => [
        'admin' => 'password123',  // Change in production!
    ],
    
    // Rate limiting
    'rate_limit' => [
        'enabled' => true,
        'max_requests' => 100,
        'window_seconds' => 60,
    ],
    
    // RBAC - protect system tables
    'rbac' => [
        'enabled' => true,
        'rules' => [
            'admin' => ['allow' => '*'],
            'user' => ['deny' => ['users', 'roles', 'permissions']],
        ],
    ],
];
```

---

## 🚀 Usage Examples

### Access Points

```bash
# upMVC - Server-side rendered pages
http://localhost:8080/myproject              → Home page
http://localhost:8080/myproject/admin        → Admin panel
http://localhost:8080/myproject/auth/login   → Login form

# API - REST endpoints
http://localhost:8080/myproject/api/index.php?action=tables      → List tables
http://localhost:8080/myproject/api/index.php?action=list&table=users → Get users
http://localhost:8080/myproject/api/dashboard.html               → API monitoring
```

### Shared Authentication Flow

#### 1. User Login via upMVC

```php
// modules/auth/Controller.php
public function login() {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Authenticate against users table
    $user = R::findOne('users', 'username = ?', [$username]);
    
    if ($user && password_verify($password, $user->password)) {
        $_SESSION['user_id'] = $user->id;
        $_SESSION['role'] = $user->role;
        // Redirect to dashboard
    }
}
```

#### 2. Get JWT Token for API

```bash
# Login to API with same credentials
curl -X POST -d "username=admin&password=password123" \
  http://localhost:8080/myproject/api/index.php?action=login

# Response:
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGci..."
}
```

#### 3. Use API with Token

```bash
# Get products via API
curl -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost:8080/myproject/api/index.php?action=list&table=products
```

### React Integration Example

```javascript
// modules/react/components/ProductList.js
import React, { useState, useEffect } from 'react';

function ProductList() {
    const [products, setProducts] = useState([]);
    
    useEffect(() => {
        // Call API from same domain
        fetch('/myproject/api/index.php?action=list&table=products', {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('jwt_token')
            }
        })
        .then(res => res.json())
        .then(data => setProducts(data.data));
    }, []);
    
    return (
        <div>
            {products.map(product => (
                <div key={product.id}>{product.name}</div>
            ))}
        </div>
    );
}
```

---

## 💎 Real-World Use Cases

### 1. E-Commerce Platform

**upMVC handles:**
- Admin panel for product management
- Order processing UI
- Customer dashboard
- Email notifications

**API provides:**
- Mobile app backend
- Product catalog API
- Shopping cart endpoints
- Payment gateway integration

### 2. SaaS Application

**upMVC handles:**
- User registration forms
- Billing dashboard
- Settings management
- Email templates

**API provides:**
- REST endpoints for integrations
- Webhook handlers
- Third-party API access
- Mobile app backend

### 3. Headless CMS

**upMVC handles:**
- Content editor interface
- Media management
- User permissions
- Workflow management

**API provides:**
- Content delivery API
- Frontend consumption
- Mobile app content
- Static site generation

---

## 🔐 Security Best Practices

### 1. Shared Database Security

```sql
-- Create dedicated database user for API (read-only for sensitive tables)
CREATE USER 'api_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT SELECT ON myproject_db.products TO 'api_user'@'localhost';
GRANT SELECT ON myproject_db.orders TO 'api_user'@'localhost';
GRANT SELECT, INSERT, UPDATE ON myproject_db.cart TO 'api_user'@'localhost';
```

### 2. JWT Secret Synchronization

Use same JWT secret in both systems for token validation:

```php
// upMVC: etc/Config.php
define('JWT_SECRET', 'your-secret-key-min-32-chars-long');

// API: config/api.php
'jwt_secret' => 'your-secret-key-min-32-chars-long',  // Same!
```

### 3. CORS Configuration (if API used from different domain)

```php
// API: config/api.php
'cors' => [
    'enabled' => true,
    'allowed_origins' => ['https://yourfrontend.com'],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE'],
],
```

### 4. Rate Limiting

```php
// API: config/api.php
'rate_limit' => [
    'enabled' => true,
    'max_requests' => 100,      // 100 requests
    'window_seconds' => 60,     // per minute
],
```

---

## 📊 Database Schema Example

### Shared Tables

```sql
-- Users table (used by both upMVC and API)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user', 'readonly') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Products table (managed via upMVC, consumed via API)
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Orders table (created via both systems)
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

---

## 🎨 Frontend Integration Patterns

### Pattern 1: Islands Architecture

Use upMVC for main layout, embed React "islands" that consume API:

```php
<!-- modules/products/templates/list.php -->
<div id="product-island"></div>

<script type="module">
    import ProductList from '/modules/react/components/ProductList.js';
    ReactDOM.render(<ProductList />, document.getElementById('product-island'));
</script>
```

### Pattern 2: Hybrid Rendering

- upMVC renders initial page (SEO-friendly)
- React takes over for interactions
- API provides data updates

```php
<!-- Server-rendered by upMVC -->
<div id="app" data-initial='<?= json_encode($products) ?>'>
    <?php foreach ($products as $product): ?>
        <div class="product"><?= $product->name ?></div>
    <?php endforeach; ?>
</div>

<script>
    // React hydrates with API calls
    const initialData = JSON.parse(document.getElementById('app').dataset.initial);
    ReactDOM.hydrate(<App initialData={initialData} />, document.getElementById('app'));
</script>
```

### Pattern 3: Pure API Frontend

Serve static React app from upMVC, consume API entirely:

```
/myproject/public/app/      → Static React build
/myproject/api/             → API backend
```

---

## 🔧 Development Workflow

### Local Development Setup

```bash
# Start upMVC on port 8080
cd /path/to/myproject
php -S localhost:8080

# Access:
# - upMVC: http://localhost:8080/myproject
# - API: http://localhost:8080/myproject/api/index.php
# - API Monitor: http://localhost:8080/myproject/api/dashboard.html
```

### Testing Both Systems

```bash
# Test upMVC
curl http://localhost:8080/myproject

# Test API
curl http://localhost:8080/myproject/api/index.php?action=tables

# Test JWT login
curl -X POST -d "username=admin&password=password123" \
  http://localhost:8080/myproject/api/index.php?action=login
```

---

## 📚 Additional Resources

### upMVC Documentation
- [upMVC README](../README.md)
- [Module Philosophy](MODULE_PHILOSOPHY.md)
- [Islands Architecture](ISLANDS_ARCHITECTURE.md)
- [React Integration Patterns](REACT_INTEGRATION_PATTERNS.md)

### PHP CRUD API Generator Documentation
- [PHP CRUD API Generator README](https://github.com/BitsHost/PHP-CRUD-API-Generator/blob/main/README.md)
- [Quick Start Guide](https://github.com/BitsHost/PHP-CRUD-API-Generator/blob/main/docs/QUICK_START.md)
- [JWT Authentication Guide](https://github.com/BitsHost/PHP-CRUD-API-Generator/blob/main/docs/JWT_EXPLAINED.md)

---

## 💡 Pro Tips

1. **Single Sign-On**: Use upMVC sessions to generate JWT tokens for API
2. **RBAC Sync**: Keep role definitions identical in both systems
3. **Logging**: Centralize logs from both systems for easier debugging
4. **Caching**: Use same cache driver (Redis/Memcached) for both
5. **Deployment**: Deploy as single application, both systems share same domain

---

## 🎯 Next Steps

1. Install both frameworks following steps above
2. Create shared database schema
3. Configure authentication
4. Build your first integrated module
5. Deploy to production!

---

**Need help?** Open an issue on:
- [upMVC GitHub](https://github.com/upMVC/upMVC/issues)
- [PHP CRUD API Generator GitHub](https://github.com/BitsHost/PHP-CRUD-API-Generator/issues)

---

Built with ❤️ by [BitsHost](https://bitshost.biz/)
