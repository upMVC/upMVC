# 💚 Integrating Pre-Built Vue Apps in upMVC Modules

## 🤔 Why Mix PHP with Vue.js?

### The Power of Polyglot Architecture

upMVC enables you to build with **Vue.js + PHP** in perfect harmony:

#### 🔐 **Layered Security Model**
- **Layer 1: Main upMVC** - Handles authentication, authorization, session management, CSRF protection
- **Layer 2: Vue Apps** - Each Vue module is isolated, progressive enhancement approach
- **Result:** Server-side security + client-side reactivity

#### 🧩 **True Micro-Frontends**
- Split applications into **independent Vue apps**
- Each module: Different Vue apps (Admin panel, User dashboard, Shop, Blog)
- Teams work independently on separate Vue apps
- Deploy modules separately without conflicts

#### ⚡ **Server-Side Rendering (SSR) Hybrid**
- **PHP generates the shell** - Fast initial load, SEO-friendly
- **Vue handles interactivity** - Reactive components, smooth UX
- **Progressive enhancement:** Works without JS, enhanced with Vue

#### 🌍 **Polyglot Development**
- **PHP** - Backend logic, database, authentication, APIs
- **Vue.js** - Modern reactive UI components
- **HTML/CSS** - Server-rendered structure
- **Mix freely** - Best tool for each job

#### 💰 **Cost & Performance Benefits**
- **No Node.js/SSR server needed** - Serve Vue SPA with PHP
- **Reduced complexity** - Build locally, deploy static files
- **CDN-friendly** - Static Vue bundles cached globally
- **Faster deployments** - `npm run build` → upload → done

---

## 🎯 Vue Build Output Structure

### Vite Build (Vue 3 Default)

```bash
npm run build
```

**Output:**
```
dist/
├── index.html                    ← Entry point
├── assets/
│   ├── index-B7G8KfXz.js        ← Main bundle (hashed)
│   ├── index-D4H2JsPa.css       ← Styles (hashed)
│   └── logo-A2Fx9D3k.svg        ← Assets (hashed)
└── vite.svg
```

### Webpack Build (Vue 2 or Custom)

```bash
npm run build
```

**Output:**
```
dist/
├── index.html
├── js/
│   ├── app.8f6d7e3a.js          ← App bundle
│   ├── chunk-vendors.2f3a8b9c.js ← Dependencies
│   └── app.8f6d7e3a.js.map      ← Source maps (optional)
├── css/
│   └── app.4c9b2d1e.css         ← Styles
└── img/
    └── logo.82b9c7a5.png        ← Assets
```

---

## 📦 Integration Methods

### Method 1: Static File Serving (Recommended)

**Best for:** Production Vue SPAs, progressive web apps, dashboards

#### Step 1: Build Your Vue App

```bash
# In your Vue project directory
npm run build

# Output goes to dist/
```

#### Step 2: Deploy to upMVC Module

```bash
# Copy build to module
cp -r dist/* /path/to/upMVC/modules/vueshop/public/
```

**Structure:**
```
modules/vueshop/
├── Controller.php          ← Serves Vue app
├── Model.php              ← API endpoints for Vue
├── View.php               ← Optional wrapper
├── routes/
│   └── Routes.php         ← Route configuration
└── public/                ← Vue build output
    ├── index.html
    ├── assets/
    │   ├── index-B7G8KfXz.js
    │   └── index-D4H2JsPa.css
```

#### Step 3: Controller.php

```php
<?php
namespace Vueshop;

class Controller {
    
    /**
     * Serve the Vue SPA entry point
     */
    public function index() {
        $indexPath = __DIR__ . '/public/index.html';
        
        if (!file_exists($indexPath)) {
            http_response_code(404);
            die('Vue app not found. Run: npm run build');
        }
        
        // Read the HTML
        $html = file_get_contents($indexPath);
        
        // Optional: Inject server data
        $userData = $this->getUserData();
        $html = str_replace(
            '</head>',
            '<script>window.__USER__ = ' . json_encode($userData) . ';</script></head>',
            $html
        );
        
        // Set proper headers
        header('Content-Type: text/html; charset=utf-8');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-Content-Type-Options: nosniff');
        
        echo $html;
    }
    
    /**
     * Serve static assets (JS, CSS, images)
     */
    public function assets($path = '') {
        // Decode URL-encoded path
        $path = urldecode($path);
        
        // Security: Prevent directory traversal
        if (strpos($path, '..') !== false) {
            http_response_code(403);
            die('Forbidden');
        }
        
        $filePath = __DIR__ . '/public/assets/' . $path;
        
        if (!file_exists($filePath) || !is_file($filePath)) {
            http_response_code(404);
            die('Asset not found');
        }
        
        // Determine MIME type
        $mimeTypes = [
            'js'   => 'application/javascript',
            'css'  => 'text/css',
            'svg'  => 'image/svg+xml',
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
            'woff' => 'font/woff',
            'woff2'=> 'font/woff2',
            'ttf'  => 'font/ttf',
            'json' => 'application/json',
        ];
        
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
        
        // Set caching headers (1 year for hashed assets)
        header('Content-Type: ' . $mimeType);
        header('Cache-Control: public, max-age=31536000, immutable');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
        
        // Output file
        readfile($filePath);
        exit;
    }
    
    /**
     * API endpoint for Vue app
     */
    public function api($action = '') {
        header('Content-Type: application/json');
        
        switch ($action) {
            case 'products':
                echo json_encode($this->getProducts());
                break;
                
            case 'cart':
                echo json_encode($this->getCart());
                break;
                
            default:
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint not found']);
        }
        exit;
    }
    
    /**
     * Get user data for injection
     */
    private function getUserData() {
        return [
            'id' => $_SESSION['user_id'] ?? null,
            'name' => $_SESSION['user_name'] ?? null,
            'isAuthenticated' => isset($_SESSION['user_id']),
            'csrfToken' => $_SESSION['csrf_token'] ?? '',
        ];
    }
    
    /**
     * Example: Get products for API
     */
    private function getProducts() {
        $model = new Model();
        return $model->getAllProducts();
    }
    
    /**
     * Example: Get cart for API
     */
    private function getCart() {
        return $_SESSION['cart'] ?? [];
    }
}
```

#### Step 4: Routes.php

```php
<?php
namespace Vueshop\Routes;

class Routes {
    private $router;
    
    public function __construct($router) {
        $this->router = $router;
    }
    
    public function initializeRoutes() {
        $base = '/vueshop';
        
        // Vue SPA entry point
        $this->router->addRoute(
            'GET', 
            $base, 
            'Vueshop\\Controller', 
            'index'
        );
        
        // Catch-all for Vue Router (history mode)
        $this->router->addRoute(
            'GET', 
            $base . '/{any}', 
            'Vueshop\\Controller', 
            'index'
        );
        
        // Static assets route
        $this->router->addRoute(
            'GET', 
            $base . '/assets/{path}', 
            'Vueshop\\Controller', 
            'assets'
        );
        
        // API endpoints
        $this->router->addRoute(
            'GET', 
            $base . '/api/{action}', 
            'Vueshop\\Controller', 
            'api'
        );
        
        $this->router->addRoute(
            'POST', 
            $base . '/api/{action}', 
            'Vueshop\\Controller', 
            'api'
        );
    }
}
```

#### Step 5: Configure Vue Router Base

In your Vue app, set the router base:

```javascript
// router/index.js (Vue 3)
import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory('/vueshop'),  // Match upMVC route base
  routes: [
    { path: '/', component: Home },
    { path: '/products', component: Products },
    { path: '/cart', component: Cart }
  ]
})

export default router
```

```javascript
// router/index.js (Vue 2)
import Vue from 'vue'
import VueRouter from 'vue-router'

Vue.use(VueRouter)

const router = new VueRouter({
  mode: 'history',
  base: '/vueshop',  // Match upMVC route base
  routes: [
    { path: '/', component: Home },
    { path: '/products', component: Products },
    { path: '/cart', component: Cart }
  ]
})

export default router
```

#### Step 6: Build Configuration

**vite.config.js (Vue 3):**
```javascript
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],
  base: '/vueshop/',  // Match upMVC route
  build: {
    outDir: 'dist',
    assetsDir: 'assets',
    // Generate manifest for SSR (optional)
    manifest: true,
    rollupOptions: {
      output: {
        // Consistent naming for easier debugging
        entryFileNames: 'assets/[name]-[hash].js',
        chunkFileNames: 'assets/[name]-[hash].js',
        assetFileNames: 'assets/[name]-[hash].[ext]'
      }
    }
  }
})
```

**vue.config.js (Vue 2/CLI):**
```javascript
module.exports = {
  publicPath: '/vueshop/',
  outputDir: 'dist',
  assetsDir: 'assets',
  
  // Generate manifest
  chainWebpack: config => {
    config.plugin('html').tap(args => {
      args[0].minify = {
        removeComments: true,
        collapseWhitespace: true,
        removeAttributeQuotes: false
      }
      return args
    })
  },
  
  // Development server proxy to upMVC API
  devServer: {
    proxy: {
      '/api': {
        target: 'http://localhost:8080',
        changeOrigin: true
      }
    }
  }
}
```

---

### Method 2: Dynamic Manifest Loading

**Best for:** Multiple Vue apps, automated deployments, when asset names change frequently

#### Controller.php

```php
<?php
namespace Vuedash;

class Controller {
    
    private $manifestPath = __DIR__ . '/public/.vite/manifest.json';
    private $manifest = null;
    
    /**
     * Load Vite manifest
     */
    private function loadManifest() {
        if ($this->manifest === null) {
            if (file_exists($this->manifestPath)) {
                $this->manifest = json_decode(
                    file_get_contents($this->manifestPath), 
                    true
                );
            } else {
                $this->manifest = [];
            }
        }
        return $this->manifest;
    }
    
    /**
     * Get asset URL from manifest
     */
    private function getAssetUrl($entry) {
        $manifest = $this->loadManifest();
        return $manifest[$entry]['file'] ?? null;
    }
    
    /**
     * Render Vue app with dynamic assets
     */
    public function index() {
        $jsFile = $this->getAssetUrl('src/main.js');
        $cssFile = $this->getAssetUrl('src/main.css');
        
        if (!$jsFile) {
            die('Vue app not built. Run: npm run build');
        }
        
        // Inject initial data
        $initialData = [
            'user' => $this->getUserData(),
            'config' => [
                'apiUrl' => BASE_URL . '/vuedash/api',
                'baseUrl' => BASE_URL . '/vuedash'
            ]
        ];
        
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Vue Dashboard</title>
            <?php if ($cssFile): ?>
                <link rel="stylesheet" href="<?= BASE_URL ?>/vuedash/assets/<?= $cssFile ?>">
            <?php endif; ?>
            <script>
                window.__INITIAL_STATE__ = <?= json_encode($initialData) ?>;
            </script>
        </head>
        <body>
            <div id="app"></div>
            <script type="module" src="<?= BASE_URL ?>/vuedash/assets/<?= $jsFile ?>"></script>
        </body>
        </html>
        <?php
    }
    
    private function getUserData() {
        return [
            'id' => $_SESSION['user_id'] ?? null,
            'name' => $_SESSION['user_name'] ?? null,
            'email' => $_SESSION['user_email'] ?? null,
            'isAuthenticated' => isset($_SESSION['user_id'])
        ];
    }
}
```

---

### Method 3: API-Driven Vue App

**Best for:** Modern SPAs, when using upMVC with PHP CRUD API Generator

#### Vue API Client Setup

```javascript
// src/services/api.js (Vue 3 Composition API)
import { ref } from 'vue'

const API_BASE = window.__INITIAL_STATE__?.config?.apiUrl || '/api'

export function useApi() {
  const loading = ref(false)
  const error = ref(null)
  
  const request = async (endpoint, options = {}) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await fetch(`${API_BASE}${endpoint}`, {
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-Token': window.__INITIAL_STATE__?.user?.csrfToken || '',
          ...options.headers
        },
        ...options
      })
      
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`)
      }
      
      return await response.json()
    } catch (err) {
      error.value = err.message
      throw err
    } finally {
      loading.value = false
    }
  }
  
  return {
    loading,
    error,
    get: (endpoint) => request(endpoint, { method: 'GET' }),
    post: (endpoint, data) => request(endpoint, { 
      method: 'POST', 
      body: JSON.stringify(data) 
    }),
    put: (endpoint, data) => request(endpoint, { 
      method: 'PUT', 
      body: JSON.stringify(data) 
    }),
    delete: (endpoint) => request(endpoint, { method: 'DELETE' })
  }
}
```

#### Using in Vue Components (Vue 3)

```vue
<template>
  <div class="products">
    <h1>Products</h1>
    
    <div v-if="loading">Loading...</div>
    <div v-else-if="error" class="error">{{ error }}</div>
    
    <div v-else class="product-grid">
      <div 
        v-for="product in products" 
        :key="product.id" 
        class="product-card"
      >
        <h3>{{ product.name }}</h3>
        <p>{{ product.price }}</p>
        <button @click="addToCart(product)">Add to Cart</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useApi } from '@/services/api'

const { loading, error, get, post } = useApi()
const products = ref([])

onMounted(async () => {
  try {
    products.value = await get('/products')
  } catch (err) {
    console.error('Failed to load products:', err)
  }
})

const addToCart = async (product) => {
  try {
    await post('/cart/add', { productId: product.id, quantity: 1 })
    alert('Added to cart!')
  } catch (err) {
    alert('Failed to add to cart')
  }
}
</script>
```

#### Using in Vue Components (Vue 2)

```vue
<template>
  <div class="products">
    <h1>Products</h1>
    
    <div v-if="loading">Loading...</div>
    <div v-else-if="error" class="error">{{ error }}</div>
    
    <div v-else class="product-grid">
      <div 
        v-for="product in products" 
        :key="product.id" 
        class="product-card"
      >
        <h3>{{ product.name }}</h3>
        <p>{{ product.price }}</p>
        <button @click="addToCart(product)">Add to Cart</button>
      </div>
    </div>
  </div>
</template>

<script>
import { useApi } from '@/services/api'

export default {
  name: 'Products',
  data() {
    return {
      products: [],
      loading: false,
      error: null
    }
  },
  async mounted() {
    await this.loadProducts()
  },
  methods: {
    async loadProducts() {
      const api = useApi()
      this.loading = true
      this.error = null
      
      try {
        this.products = await api.get('/products')
      } catch (err) {
        this.error = err.message
      } finally {
        this.loading = false
      }
    },
    async addToCart(product) {
      const api = useApi()
      try {
        await api.post('/cart/add', { 
          productId: product.id, 
          quantity: 1 
        })
        alert('Added to cart!')
      } catch (err) {
        alert('Failed to add to cart')
      }
    }
  }
}
</script>
```

---

## 🚀 Build & Deployment Workflow

### Development

```bash
# Terminal 1: Vue dev server
cd vue-app
npm run dev
# Runs on http://localhost:5173

# Terminal 2: upMVC server
cd /path/to/upMVC
php -S localhost:8080

# Configure proxy in vite.config.js to call upMVC API
```

### Production Build

```bash
# 1. Build Vue app
cd vue-app
npm run build

# 2. Deploy to upMVC module
cp -r dist/* ../upMVC/modules/vueshop/public/

# 3. Test in production mode
cd ../upMVC
php -S localhost:8080

# 4. Visit http://localhost:8080/vueshop
```

### Automated Deployment Script

**deploy.sh:**
```bash
#!/bin/bash

# Configuration
VUE_APP_DIR="./vue-shop"
UPMVC_MODULE="./upMVC/modules/vueshop"
BUILD_DIR="$VUE_APP_DIR/dist"
DEPLOY_DIR="$UPMVC_MODULE/public"

echo "🚀 Starting deployment..."

# Build Vue app
echo "📦 Building Vue app..."
cd $VUE_APP_DIR
npm run build

if [ $? -ne 0 ]; then
    echo "❌ Build failed!"
    exit 1
fi

# Clear old deployment
echo "🧹 Clearing old files..."
rm -rf $DEPLOY_DIR/*

# Copy new build
echo "📋 Copying build files..."
cp -r $BUILD_DIR/* $DEPLOY_DIR/

# Set permissions
chmod -R 755 $DEPLOY_DIR

echo "✅ Deployment complete!"
echo "📍 Deployed to: $DEPLOY_DIR"
echo "🌐 Access at: http://yoursite.com/vueshop"
```

Make it executable:
```bash
chmod +x deploy.sh
./deploy.sh
```

---

## 📊 Comparison Table

| Method | Best For | Pros | Cons |
|--------|----------|------|------|
| **Static Serving** | Production SPAs, PWAs | Simple, fast, CDN-friendly | Manual index.html serving |
| **Manifest Loading** | Multiple apps, CI/CD | Auto asset discovery, flexible | Requires manifest parsing |
| **API-Driven** | Modern architecture | Clean separation, scalable | More initial setup |
| **All Combined** | Enterprise apps | Maximum flexibility | Higher complexity |

---

## 💡 Pro Tips

### 1. Environment Variables

**Vue app (.env.production):**
```bash
VITE_API_URL=/vueshop/api
VITE_BASE_URL=/vueshop
```

**Access in Vue:**
```javascript
const apiUrl = import.meta.env.VITE_API_URL
```

### 2. Cache Busting

Vite/Webpack automatically adds hashes to filenames:
```
index-B7G8KfXz.js  ← Hash changes on rebuild
```

This ensures browsers always get the latest version.

### 3. Multiple Vue Apps

Run multiple independent Vue apps:

```
modules/
├── vueshop/        ← E-commerce (Vue 3 + Vite)
├── vuedash/        ← Admin panel (Vue 3 + Composition API)
├── vueblog/        ← Blog (Vue 2 + Nuxt static)
└── vuechat/        ← Real-time chat (Vue 3 + Socket.io)
```

Each has its own route base and isolated state.

### 4. Development Proxy

In **vite.config.js**, proxy API calls to upMVC:

```javascript
export default defineConfig({
  server: {
    proxy: {
      '/api': {
        target: 'http://localhost:8080',
        changeOrigin: true,
        rewrite: (path) => path.replace(/^\/api/, '/vueshop/api')
      }
    }
  }
})
```

This lets you develop Vue on `:5173` while calling upMVC API on `:8080`.

### 5. Build Optimization

**vite.config.js:**
```javascript
export default defineConfig({
  build: {
    // Reduce chunk size
    chunkSizeWarningLimit: 1000,
    rollupOptions: {
      output: {
        manualChunks: {
          'vendor': ['vue', 'vue-router', 'pinia'],
        }
      }
    },
    // Enable compression
    minify: 'terser',
    terserOptions: {
      compress: {
        drop_console: true,  // Remove console.logs
        drop_debugger: true
      }
    }
  }
})
```

### 6. TypeScript Support

Vue 3 + TypeScript works perfectly:

```bash
npm create vite@latest vue-app -- --template vue-ts
```

No changes needed in upMVC integration!

### 7. Pinia State Management

**store/index.js:**
```javascript
import { createPinia } from 'pinia'
import { defineStore } from 'pinia'

export const useUserStore = defineStore('user', {
  state: () => ({
    user: window.__INITIAL_STATE__?.user || null
  }),
  getters: {
    isAuthenticated: (state) => !!state.user?.id
  },
  actions: {
    async login(credentials) {
      const api = useApi()
      const data = await api.post('/auth/login', credentials)
      this.user = data.user
    }
  }
})
```

### 8. SSR with Nitro (Advanced)

For full SSR, consider Nuxt.js with upMVC as API backend:

```
modules/
├── nuxtblog/      ← Nuxt SSR app
│   ├── Controller.php   ← Proxies to Nuxt server
│   └── nuxt-app/        ← Nuxt source
└── api/           ← upMVC API endpoints
```

---

## 🎯 Recommended Approach

**For most projects:** Combine **Method 1 (Static Serving)** + **Method 3 (API-Driven)**

1. Build Vue app → static files
2. PHP serves `index.html` and assets
3. Vue app consumes upMVC API endpoints
4. PHP handles auth, sessions, database
5. Vue handles UI, interactivity, routing

**Benefits:**
✅ Simple deployment (static files)  
✅ Fast performance (cached assets)  
✅ Clean separation (PHP = backend, Vue = frontend)  
✅ Scalable architecture (can split to microservices later)  
✅ SEO-friendly (server-rendered shell + Vue hydration)

---

## 🔗 Related Documentation

- [React Build Integration](REACT_BUILD_INTEGRATION.md) - Similar patterns for React
- [Islands Architecture](ISLANDS_ARCHITECTURE_INDEX.md) - PHP + Vue Islands pattern
- [Integration: upMVC + PHP CRUD API](INTEGRATION_PHP_CRUD_API.md) - Full-stack guide
- [Module Philosophy](MODULE_PHILOSOPHY.md) - Understanding upMVC modules

---

**Happy coding with Vue + upMVC!** 💚🚀
