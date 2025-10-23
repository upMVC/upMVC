# 🎨 Integrating Pre-Built React Apps in upMVC Modules

## 🤔 Why Mix PHP with JavaScript Frameworks?

### The Power of Polyglot Architecture

upMVC isn't a framework—it's a **system architecture** that enables you to build complex applications using the best tool for each job:

#### 🔐 **Layered Security Model**
- **Layer 1: Main upMVC** - Handles authentication, authorization, session management, CSRF protection
- **Layer 2: Module Apps** - Each module (React, Vue, Svelte) is isolated, can't compromise the system
- **Result:** One breach doesn't bring down the entire application

#### 🧩 **True Micro-Frontends**
- Split large applications into **smaller, independent apps**
- Each module can use **different technologies**: PHP for CMS, React for dashboard, Vue for shop, Svelte for chat
- Teams work independently without conflicts
- Deploy modules separately without affecting others

#### ⚡ **Server-Side Rendering (SSR) Hybrid**
- **PHP generates the shell** - Fast initial page load, SEO-friendly HTML
- **JavaScript handles interactivity** - Dynamic updates without page reloads
- **Best of both worlds:** Speed + Interactivity + SEO

#### 🌍 **Polyglot Development**
Not limited to one language or framework:
- **PHP** - Business logic, database operations, authentication
- **JavaScript** - Client-side interactivity
- **React/Vue/Svelte** - Modern UI components
- **HTML/CSS** - Structure and styling
- **Mix freely** - Use what works best for each feature

#### 🆓 **True Freedom - No Framework Rules**
upMVC follows the **"Direct PHP First"** principle:
- **No forced conventions** - Write code your way
- **No abstractions** - Direct PHP, direct database access, no ORM requirements
- **No magic** - You see what you write, you write what you see
- **No lock-in** - Use any library, any pattern, any architecture
- **Freedom to choose** - Pure PHP, modern frameworks, or mix both

#### 💰 **Cost & Performance Benefits**
- **No Node.js servers needed** - Serve static JS bundles with PHP
- **Reduced infrastructure** - One PHP server handles everything
- **Better caching** - Static assets cached by CDN/browser
- **Faster deployments** - Build once, deploy static files

### Use Cases

✅ **E-commerce Platform:** PHP for checkout/payments + React for product catalog + Vue for admin panel  
✅ **SaaS Application:** PHP for auth/billing + Svelte for dashboard + React for reports  
✅ **Content Platform:** PHP CMS + Multiple React apps for different sections  
✅ **Enterprise System:** Legacy PHP modules + Modern JS frameworks gradually replacing old code

---

## Overview

This guide shows how to integrate React applications that have been built (compiled) using `npm run build` into upMVC modules. This approach is ideal for production deployments where you want to serve optimized, minified React bundles.

---

## 🎯 Current Implementation Analysis

### Existing Modules: `reactb` and `reactcrud`

**Current Approach:**
```
modules/reactb/
├── Controller.php          ← Manual methods for each asset
├── View.php               ← HTML with hardcoded asset paths
├── Model.php
├── routes/Routes.php
└── etc/build/             ← React build output
    ├── index.html
    ├── manifest.json
    ├── static/
    │   ├── js/
    │   │   └── main.10d2eb17.js
    │   └── css/
    │       └── main.f855e6bc.css
```

**How it works:**
1. React app built with `npm run build`
2. Build files copied to `etc/build/`
3. Controller creates methods for each asset:
```php
public function mainjs() {
    require_once THIS_DIR . "/modules/reactb/etc/build/static/js/main.10d2eb17.js";
}

public function maincss() {
    require_once THIS_DIR . "/modules/reactb/etc/build/static/css/main.f855e6bc.css";
}
```
4. View includes assets via BASE_URL routes

**Problems:**
- ❌ Manual controller method for each asset
- ❌ Hard-coded filenames (breaks when React rebuilds with new hashes)
- ❌ Requires route for every single file
- ❌ No cache headers for performance
- ❌ Difficult to update (must change routes)

---

## 💡 Improved Integration Methods

### **Method 1: Static File Serving (Simplest & Recommended)**

**Best for:** Most production React apps

#### Structure:
```
modules/myreactapp/
├── Controller.php          ← Serves index.html
├── View.php               ← Optional (or serve directly)
├── Model.php              ← Backend logic if needed
├── routes/Routes.php      ← Main app route + static file handler
└── public/                ← React build output (npm run build)
    ├── index.html
    ├── manifest.json
    ├── asset-manifest.json
    ├── static/
    │   ├── js/
    │   │   ├── main.[hash].js
    │   │   └── [chunk].[hash].js
    │   ├── css/
    │   │   └── main.[hash].css
    │   └── media/
    └── assets/
```

#### Controller.php:
```php
<?php
namespace Myreactapp;

class Controller
{
    /**
     * Serve the React app's index.html
     */
    public function display($reqRoute, $reqMet)
    {
        $buildPath = __DIR__ . '/public/index.html';
        
        if (!file_exists($buildPath)) {
            echo "<h1>React app not built</h1>";
            echo "<p>Run: <code>npm run build</code> in your React project</p>";
            return;
        }
        
        // Read the built index.html
        $html = file_get_contents($buildPath);
        
        // Fix asset paths to work with upMVC routing
        // React uses absolute paths like /static/js/main.js
        // We need /myreactapp/public/static/js/main.js
        $basePath = BASE_URL . '/myreactapp/public';
        
        $html = str_replace('="/static/', '="' . $basePath . '/static/', $html);
        $html = str_replace('="/manifest.json', '="' . $basePath . '/manifest.json', $html);
        
        // Output the modified HTML
        echo $html;
    }
    
    /**
     * Serve static files (JS, CSS, images, etc.)
     */
    public function serveStatic()
    {
        // Get requested file from URL
        // Example: /myreactapp/public/static/js/main.abc123.js
        $requestUri = $_SERVER['REQUEST_URI'];
        $pattern = '/\/myreactapp\/public\/(.*)/';
        
        if (preg_match($pattern, $requestUri, $matches)) {
            $filePath = __DIR__ . '/public/' . $matches[1];
            
            // Security: Prevent directory traversal
            $realPath = realpath($filePath);
            $publicDir = realpath(__DIR__ . '/public');
            
            if ($realPath && strpos($realPath, $publicDir) === 0 && file_exists($realPath)) {
                // Determine MIME type
                $mime = $this->getMimeType($realPath);
                
                // Set headers
                header('Content-Type: ' . $mime);
                header('Cache-Control: public, max-age=31536000'); // 1 year cache
                header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
                
                // Output file
                readfile($realPath);
                exit;
            }
        }
        
        // File not found
        http_response_code(404);
        echo "File not found";
    }
    
    /**
     * Get MIME type for file
     */
    private function getMimeType($filePath)
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        
        $mimeTypes = [
            'js' => 'application/javascript',
            'css' => 'text/css',
            'json' => 'application/json',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'eot' => 'application/vnd.ms-fontobject',
        ];
        
        return $mimeTypes[$extension] ?? mime_content_type($filePath) ?? 'application/octet-stream';
    }
}
```

#### routes/Routes.php:
```php
<?php
use upMVC\Router;

// Main React app route
$router->addRoute('GET', '/myreactapp', ['Myreactapp\Controller', 'display']);

// Serve all static files
$router->addRoute('GET', '/myreactapp/public/*', ['Myreactapp\Controller', 'serveStatic']);
```

#### Build & Deploy Process:
```bash
# 1. Develop your React app
cd /path/to/react-app
npm install
npm start  # Development

# 2. Build for production
npm run build

# 3. Copy build to upMVC module
cp -r build/* /path/to/upMVC/modules/myreactapp/public/

# 4. Access in browser
# http://localhost/myproject/myreactapp
```

---

### **Method 2: Dynamic Asset Loading (Production-Ready)**

**Best for:** Apps that rebuild frequently, need dynamic asset injection

Uses React's `asset-manifest.json` to automatically load correct files:

#### Controller.php:
```php
<?php
namespace Myreactapp;

class Controller
{
    /**
     * Load asset manifest to get current file hashes
     */
    private function getAssetManifest()
    {
        $manifestPath = __DIR__ . '/public/asset-manifest.json';
        
        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);
            return $manifest['files'] ?? $manifest;
        }
        
        return null;
    }
    
    public function display($reqRoute, $reqMet)
    {
        $manifest = $this->getAssetManifest();
        
        if (!$manifest) {
            echo "React app not built. Run: npm run build";
            return;
        }
        
        $view = new View();
        $view->render($manifest);
    }
}
```

#### View.php:
```php
<?php
namespace Myreactapp;

use Common\Bmvc\BaseView;

class View extends BaseView
{
    public function render($manifest)
    {
        $basePath = BASE_URL . '/myreactapp/public';
        
        $this->startHead('My React App');
        
        // Load CSS files from manifest
        foreach ($manifest as $key => $file) {
            if (strpos($key, '.css') !== false && strpos($key, 'map') === false) {
                echo '<link href="' . $basePath . $file . '" rel="stylesheet">';
            }
        }
        
        $this->endHead();
        $this->startBody('My React App');
        
        // React mount point
        echo '<div id="root"></div>';
        
        // Load JS files from manifest
        // Note: Load runtime first, then main
        $jsFiles = [];
        foreach ($manifest as $key => $file) {
            if (strpos($key, '.js') !== false && strpos($key, 'map') === false) {
                if (strpos($key, 'runtime-main') !== false) {
                    array_unshift($jsFiles, $file); // Runtime first
                } else {
                    $jsFiles[] = $file;
                }
            }
        }
        
        foreach ($jsFiles as $file) {
            echo '<script src="' . $basePath . $file . '"></script>';
        }
        
        $this->endBody();
    }
}
```

---

### **Method 3: Hybrid with Server Data Injection**

**Best for:** Apps that need initial server-side data (SEO, performance)

#### Controller.php:
```php
<?php
namespace Myreactapp;

class Controller
{
    public function display($reqRoute, $reqMet)
    {
        // Get initial data from database
        $model = new Model();
        $initialData = [
            'user' => $model->getCurrentUser(),
            'products' => $model->getProducts(),
            'config' => [
                'apiUrl' => BASE_URL . '/api',
                'baseUrl' => BASE_URL,
            ]
        ];
        
        $manifest = $this->getAssetManifest();
        $view = new View();
        $view->render($manifest, $initialData);
    }
    
    private function getAssetManifest()
    {
        $manifestPath = __DIR__ . '/public/asset-manifest.json';
        return file_exists($manifestPath) 
            ? json_decode(file_get_contents($manifestPath), true)['files'] 
            : null;
    }
}
```

#### View.php:
```php
<?php
namespace Myreactapp;

use Common\Bmvc\BaseView;

class View extends BaseView
{
    public function render($manifest, $initialData)
    {
        $basePath = BASE_URL . '/myreactapp/public';
        
        $this->startHead('My React App');
        
        // Inject initial state for React
        echo '<script>';
        echo 'window.__INITIAL_STATE__ = ' . json_encode($initialData) . ';';
        echo 'window.BASE_URL = "' . BASE_URL . '";';
        echo '</script>';
        
        // Load CSS
        foreach ($manifest as $key => $file) {
            if (strpos($key, '.css') !== false && strpos($key, 'map') === false) {
                echo '<link href="' . $basePath . $file . '" rel="stylesheet">';
            }
        }
        
        $this->endHead();
        $this->startBody('My React App');
        
        echo '<div id="root"></div>';
        
        // Load JS
        foreach ($manifest as $key => $file) {
            if (strpos($key, '.js') !== false && strpos($key, 'map') === false) {
                echo '<script src="' . $basePath . $file . '"></script>';
            }
        }
        
        $this->endBody();
    }
}
```

#### React App (src/index.js):
```javascript
import React from 'react';
import ReactDOM from 'react-dom';
import App from './App';

// Get initial state injected by PHP
const initialState = window.__INITIAL_STATE__ || {};
const baseUrl = window.BASE_URL || '';

ReactDOM.render(
    <App initialState={initialState} baseUrl={baseUrl} />,
    document.getElementById('root')
);
```

---

### **Method 4: API-Driven React App**

**Best for:** Modern SPAs that consume REST APIs

#### Structure:
```
modules/myreactapp/
├── Controller.php          ← Serves React build
├── View.php               ← Loads React assets
├── routes/Routes.php      ← /myreactapp route
└── public/                ← React build (uses /api for data)
```

#### React App Configuration (during development):

**src/api/config.js:**
```javascript
// API configuration
const API_BASE = process.env.REACT_APP_API_URL || '/api/index.php';
const BASE_URL = process.env.PUBLIC_URL || '';

export const api = {
    baseUrl: API_BASE,
    endpoints: {
        products: `${API_BASE}?action=list&table=products`,
        users: `${API_BASE}?action=list&table=users`,
        orders: `${API_BASE}?action=list&table=orders`,
    }
};

export { BASE_URL };
```

**src/api/client.js:**
```javascript
import { api } from './config';

class ApiClient {
    constructor() {
        this.token = localStorage.getItem('jwt_token');
    }
    
    async request(url, options = {}) {
        const headers = {
            'Content-Type': 'application/json',
            ...options.headers,
        };
        
        if (this.token) {
            headers['Authorization'] = `Bearer ${this.token}`;
        }
        
        const response = await fetch(url, {
            ...options,
            headers,
        });
        
        if (!response.ok) {
            throw new Error(`API Error: ${response.statusText}`);
        }
        
        return response.json();
    }
    
    async getProducts() {
        return this.request(api.endpoints.products);
    }
    
    async createProduct(data) {
        return this.request(api.endpoints.products.replace('list', 'create'), {
            method: 'POST',
            body: JSON.stringify(data),
        });
    }
    
    async login(username, password) {
        const url = `${api.baseUrl}?action=login`;
        const data = await this.request(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `username=${username}&password=${password}`,
        });
        
        if (data.token) {
            localStorage.setItem('jwt_token', data.token);
            this.token = data.token;
        }
        
        return data;
    }
}

export default new ApiClient();
```

**src/App.js:**
```javascript
import React, { useState, useEffect } from 'react';
import api from './api/client';

function App() {
    const [products, setProducts] = useState([]);
    const [loading, setLoading] = useState(true);
    
    useEffect(() => {
        loadProducts();
    }, []);
    
    const loadProducts = async () => {
        try {
            const data = await api.getProducts();
            setProducts(data.data || []);
        } catch (error) {
            console.error('Error loading products:', error);
        } finally {
            setLoading(false);
        }
    };
    
    if (loading) return <div>Loading...</div>;
    
    return (
        <div className="App">
            <h1>Products</h1>
            <ul>
                {products.map(product => (
                    <li key={product.id}>{product.name}</li>
                ))}
            </ul>
        </div>
    );
}

export default App;
```

**package.json:**
```json
{
  "name": "myreactapp",
  "version": "0.1.0",
  "homepage": "/myreactapp",
  "proxy": "http://localhost:8080",
  "scripts": {
    "start": "react-scripts start",
    "build": "react-scripts build",
    "deploy": "npm run build && cp -r build/* ../public/"
  }
}
```

---

## 🚀 Complete Workflow

### Development Phase:

```bash
# 1. Create React app
npx create-react-app my-react-app
cd my-react-app

# 2. Configure for upMVC
# Edit package.json - add homepage
{
  "homepage": "/myreactapp"
}

# 3. Develop with hot reload
npm start
# Access: http://localhost:3000

# 4. Configure API calls (if using PHP CRUD API Generator)
# Create src/api/config.js and src/api/client.js (see Method 4 above)
```

### Build & Deploy:

```bash
# 1. Build React app
npm run build

# 2. Copy to upMVC module
# Option A: Manual copy
cp -r build/* /path/to/upMVC/modules/myreactapp/public/

# Option B: NPM script (add to package.json)
"scripts": {
  "deploy": "npm run build && cp -r build/* /path/to/upMVC/modules/myreactapp/public/"
}

# Then run:
npm run deploy

# 3. Access in browser
# http://localhost/myproject/myreactapp
```

### Automated Deployment Script:

**deploy.sh:**
```bash
#!/bin/bash

# Build React app
echo "Building React app..."
npm run build

# Copy to upMVC module
echo "Deploying to upMVC..."
rm -rf /path/to/upMVC/modules/myreactapp/public/*
cp -r build/* /path/to/upMVC/modules/myreactapp/public/

echo "✅ Deployment complete!"
echo "Access at: http://localhost/myproject/myreactapp"
```

---

## 📊 Comparison: Methods

| Method | Complexity | Flexibility | Best For |
|--------|-----------|-------------|----------|
| **Method 1: Static Serving** | ⭐ Low | ⭐⭐⭐ High | Most apps |
| **Method 2: Dynamic Assets** | ⭐⭐ Medium | ⭐⭐⭐⭐ Very High | Frequent rebuilds |
| **Method 3: Server Injection** | ⭐⭐⭐ High | ⭐⭐⭐⭐⭐ Maximum | SEO, performance |
| **Method 4: API-Driven** | ⭐⭐ Medium | ⭐⭐⭐⭐⭐ Maximum | Modern SPAs |

---

## 🎯 Recommended Approach

**For most production React apps:**

✅ **Use Method 1 (Static Serving) + Method 4 (API-Driven)**

**Why?**
- Simple to implement
- Works with any React build
- No hardcoded filenames
- Easy to update (just rebuild React)
- Clean separation (React = UI, API = Data)
- Production-ready with caching

**Structure:**
```
modules/myreactapp/
├── Controller.php          ← Serves index.html + static files
├── routes/Routes.php       ← /myreactapp + /myreactapp/public/*
└── public/                 ← React build (npm run build)
    ├── index.html
    ├── asset-manifest.json
    └── static/
        ├── js/
        └── css/
```

React app consumes API at `/api/index.php` (PHP CRUD API Generator)

---

## 💡 Pro Tips

### 1. Environment Variables in React

**.env (React project root):**
```bash
REACT_APP_API_URL=/api/index.php
PUBLIC_URL=/myreactapp
```

**Access in React:**
```javascript
const apiUrl = process.env.REACT_APP_API_URL;
```

### 2. Build Optimization

**package.json:**
```json
{
  "scripts": {
    "build": "GENERATE_SOURCEMAP=false react-scripts build"
  }
}
```

### 3. Cache Busting

React automatically adds hashes to filenames:
- `main.abc123.js` ← Hash changes on each build
- Browser automatically fetches new version
- No manual cache clearing needed!

### 4. Multiple React Apps

You can have multiple React modules:
```
modules/
├── shop-react/          ← E-commerce frontend
├── admin-react/         ← Admin dashboard
└── blog-react/          ← Blog interface
```

Each with its own route:
- `/shop`
- `/admin`
- `/blog`

### 5. Development Proxy

When developing React (npm start), use proxy to avoid CORS:

**package.json:**
```json
{
  "proxy": "http://localhost:8080"
}
```

Now API calls work in development:
```javascript
fetch('/api/index.php?action=list&table=products')
// Proxies to: http://localhost:8080/api/index.php?action=list&table=products
```

---

## 🔗 Related Documentation

- [Islands Architecture](ISLANDS_ARCHITECTURE.md) - Hybrid React + PHP rendering
- [React Integration Patterns](REACT_INTEGRATION_PATTERNS.md) - Multiple integration approaches
- [ReactHMR Module](../modules/reacthmr/README.md) - Development with hot reload
- [Integration: upMVC + PHP CRUD API Generator](INTEGRATION_PHP_CRUD_API.md) - Full-stack setup

---

Built with ❤️ by [BitsHost](https://bitshost.biz/)
