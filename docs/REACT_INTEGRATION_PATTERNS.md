# React/JS Framework Integration Patterns in upMVC

## 🎯 Overview

upMVC demonstrates **five different approaches** to integrating modern JavaScript frameworks (React, Vue, Preact) with PHP backend. Each pattern serves different use cases and complexity levels.

---

## 📊 Pattern Comparison

| Module | Pattern | Build Step | HMR | Use Case | Complexity |
|--------|---------|------------|-----|----------|------------|
| **react** | CDN Components | ❌ No | ❌ | Simple interactive widgets | Low |
| **reactb** | Built React App (Embedded) | ✅ Yes | ⚠️ | Full React SPA in PHP page section | Medium |
| **reactcrud** | Built React App (Full Page) | ✅ Yes | ⚠️ | Complete React SPA with PHP backend | High |
| **reactnb** | ES Modules (No Build) | ❌ No | ❌ | Modern JS without tooling | Medium |
| **reacthmr** | ES Modules + HMR | ❌ No | ✅ | Development with auto-reload | Medium |

---

## 🔷 Pattern 1: CDN Components (`src/Modules/React`)

### Philosophy: "PHP Islands in React"
Load React from CDN and add small interactive components to PHP pages.

### Implementation

**View.php:**
```php
<!-- Load React from CDN -->
<script src="https://unpkg.com/react@18/umd/react.development.js" crossorigin></script>
<script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js" crossorigin></script>

<!-- Container for React component -->
<div id="like_button_container"></div>

<!-- Load custom component from PHP route -->
<script src="<?php echo \BASE_URL; ?>/comp"></script>
```

**Controller.php:**
```php
case "/react":
    $this->main($reqMet, $reqRoute);
    break;
case "/comp":
    $this->comp($reqMet);  // Serves component.js
    break;

private function comp($reqMet)
{
    require_once THIS_DIR . "/Modules/react/etc/component.js";
}
```

**etc/component.js:**
```javascript
'use strict';

const e = React.createElement;

class LikeButton extends React.Component {
  constructor(props) {
    super(props);
    this.state = { liked: false };
  }

  render() {
    if (this.state.liked) {
      return 'You liked this.';
    }

    return e(
      'button',
      { onClick: () => this.setState({ liked: true }) },
      'Like'
    );
  }
}

const domContainer = document.querySelector('#like_button_container');
const root = ReactDOM.createRoot(domContainer);
root.render(e(LikeButton));
```

### Characteristics

**Pros:**
- ✅ No build step required
- ✅ Simple to understand
- ✅ Quick prototyping
- ✅ Perfect for adding interactivity to existing PHP pages
- ✅ Minimal setup

**Cons:**
- ⚠️ Can't use JSX (must use `React.createElement`)
- ⚠️ Limited to simple components
- ⚠️ CDN dependency
- ⚠️ Not suitable for complex apps

**Use When:**
- Adding interactive widgets to PHP pages
- Simple forms, buttons, counters
- Learning React basics
- Prototyping quickly

---

## 🔷 Pattern 2: Built React App - Embedded (`src/Modules/Reactb`)

### Philosophy: "React Islands in PHP"
Build a complete React app, then embed it in a **section** of a PHP page.

### Implementation

**Structure:**
```
reactb/
├── Controller.php
├── View.php
├── routes/Routes.php
└── etc/
    └── build/              ← Output from `npm run build`
        ├── index.html      ← Reference for asset paths
        ├── manifest.json
        ├── logo192.png
        └── static/
            ├── js/
            │   └── main.10d2eb17.js
            └── css/
                └── main.f855e6bc.css
```

**Controller.php** - Serves built assets via PHP routes:
```php
public function display($reqRoute, $reqMet)
{
    $view = new View();
    $view->View($reqMet);
}

public function logo()
{
    require_once THIS_DIR . "/Modules/reactb/etc/build/logo192.png";
}

public function manifest()
{
    require_once THIS_DIR . "/Modules/reactb/etc/build/manifest.json";
}

public function mainjs()
{
    require_once THIS_DIR . "/Modules/reactb/etc/build/static/js/main.10d2eb17.js";
}

public function maincss()
{
    require_once THIS_DIR . "/Modules/reactb/etc/build/static/css/main.f855e6bc.css";
}
```

**View.php** - Embeds React in a PHP layout:
```php
<?php
// PHP head section
$this->startHead($title);
?>

<!-- React app assets from build -->
<meta charset="utf-8" />
<meta name="theme-color" content="#000000" />
<link rel="apple-touch-icon" href="<?php echo \BASE_URL; ?>/logo" />
<link rel="manifest" href="<?php echo \BASE_URL; ?>/manifest" />
<script defer="defer" src="<?php echo \BASE_URL; ?>/mainjs"></script>
<link href="<?php echo \BASE_URL; ?>/maincss" rel="stylesheet">

<?php
$this->endHead();
$this->startBody($title);
?>

<!-- PHP layout with React in middle column -->
<div class="row">
    <div class="column left" style="background-color:#FFA500;">
        <h2>PHP Column 1</h2>
    </div>
    
    <div id="root" class="column middle">
        <!-- React app renders here -->
    </div>
    
    <div class="column right" style="background-color:#FFA500;">
        <h2>PHP Column 3</h2>
    </div>
</div>

<?php
$this->endBody();
?>
```

**routes/Routes.php:**
```php
$router->addRoute("/reactb", Controller::class, "display");
$router->addRoute("/reactb/logo", Controller::class, "logo");
$router->addRoute("/reactb/manifest", Controller::class, "manifest");
$router->addRoute("/reactb/mainjs", Controller::class, "mainjs");
$router->addRoute("/reactb/maincss", Controller::class, "maincss");
```

### Build Process

1. **Develop React app separately:**
   ```bash
   cd modules/reactb/etc/react-app
   npm install
   npm start  # Development
   ```

2. **Build for production:**
   ```bash
   npm run build
   # Outputs to: modules/reactb/etc/build/
   ```

3. **Extract asset paths from `build/index.html`:**
   ```html
   <!-- Copy these paths to View.php -->
   <script defer="defer" src="/static/js/main.10d2eb17.js"></script>
   <link href="/static/css/main.f855e6bc.css" rel="stylesheet">
   ```

4. **Create PHP routes** for each asset in Controller.php

### Characteristics

**Pros:**
- ✅ Full React features (JSX, hooks, npm packages)
- ✅ Can use modern tooling (webpack, babel)
- ✅ Optimized production build
- ✅ React component library access
- ✅ Mix PHP and React UI on same page

**Cons:**
- ⚠️ Build step required
- ⚠️ Must update asset paths after each build
- ⚠️ Need Node.js/npm installed
- ⚠️ Multiple PHP routes for assets

**Use When:**
- Need a complex React component in part of a PHP page
- Want dashboard widgets powered by React
- Building hybrid PHP/React applications
- Need React ecosystem but keep PHP backend

---

## 🔷 Pattern 3: Built React App - Full Page (`src/Modules/Reactcrud`)

### Philosophy: "Full React SPA with PHP Backend"
Build a complete React SPA that takes over the **entire page**, with PHP serving only as API backend.

### Implementation

**Controller.php** - Serves all React assets:
```php
public function display($reqRoute, $reqMet)
{
    $view = new View();
    $view->View($reqMet);
}

public function css()
{
    require_once THIS_DIR . "/Modules/reactcrud/etc/build/static/css/2.326c04ff.chunk.css";
}

public function cssb()
{
    require_once THIS_DIR . "/Modules/reactcrud/etc/build/static/css/main.b1413f35.chunk.css";
}

public function js()
{
    require_once THIS_DIR . "/Modules/reactcrud/etc/build/static/js/2.b5004239.chunk.js";
}

public function jsb()
{
    require_once THIS_DIR . "/Modules/reactcrud/etc/build/static/js/main.dc560686.chunk.js";
}
```

**View.php** - Minimal PHP wrapper:
```php
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="theme-color" content="#000000" />
    <link rel="manifest" href="<?php echo \BASE_URL; ?>/crud/manifest" />
    <title>CRUD React App</title>
    
    <!-- PHP menu/header -->
    <?php $this->menuCssCustom(); ?>
    
    <!-- React app CSS -->
    <link href="<?php echo \BASE_URL; ?>/crud/css" rel="stylesheet">
    <link href="<?php echo \BASE_URL; ?>/crud/cssb" rel="stylesheet">
    
    <?php $this->menu() ?>
</head>

<body>
    <noscript>You need to enable JavaScript to run this app.</noscript>
    
    <!-- React takes over entire content area -->
    <div id="root" style="width: 1300px; margin: 5;"></div>
    
    <!-- Webpack runtime bootstrap -->
    <script>
        // ... webpack runtime code ...
    </script>
    
    <!-- React app bundles -->
    <script src="<?php echo \BASE_URL; ?>/crud/js"></script>
    <script src="<?php echo \BASE_URL; ?>/crud/jsa"></script>
</body>
```

**routes/Routes.php:**
```php
$router->addRoute("/crud", Controller::class, "display");
$router->addRoute("/crud/manifest", Controller::class, "manifest");
$router->addRoute("/crud/css", Controller::class, "css");
$router->addRoute("/crud/cssb", Controller::class, "cssb");
$router->addRoute("/crud/js", Controller::class, "js");
$router->addRoute("/crud/jsa", Controller::class, "jsb");
```

### Characteristics

**Pros:**
- ✅ Full React SPA experience
- ✅ Complete React Router support
- ✅ All npm packages available
- ✅ PHP handles only API/auth
- ✅ Modern SPA architecture

**Cons:**
- ⚠️ Build step required
- ⚠️ More complex asset management
- ⚠️ Requires code splitting understanding
- ⚠️ Must handle routing correctly

**Use When:**
- Building a full SPA with PHP API
- Need complete React ecosystem
- Want client-side routing
- Complex CRUD applications

---

## 🔷 Pattern 4: ES Modules - No Build (`src/Modules/Reactnb`)

### Philosophy: "Modern JS Frameworks WITHOUT Build Step"
Use modern JavaScript with ES modules, import maps, and framework CDNs. No webpack, no babel, no npm build.

### Implementation

**View.php** - Multiple frameworks, no build:
```php
<?php
$view->startHead($this->title);
?>

<!-- Import Maps for modern ES modules -->
<script type="importmap">
{
  "imports": {
    "preact": "https://esm.sh/preact@10.23.1",
    "htm/preact": "https://esm.sh/htm@3.1.1/preact?external=preact",
    "preact/": "https://esm.sh/preact@10.23.1/",
    "react-dom": "https://esm.sh/preact@10.23.1/compat",
    "@mui/material": "https://esm.sh/@mui/material@5.16.7?external=react,react-dom",
    "@preact/signals": "https://esm.sh/@preact/signals@1.3.0?external=preact"
  }
}
</script>

<!-- Vue from CDN -->
<script src="https://unpkg.com/vue@3.2.47"></script>

<?php
$view->endHead();
$view->startBody($this->title);
?>

<body>
    <!-- Preact Example -->
    <div class="container">
        <h3>PHP and Preact - NO Build step</h3>
        <div id="app"></div>
        <div id="counter"></div>
    </div>
    
    <!-- Vue Example -->
    <div class="container">
        <h3>PHP and VUE - NO Build step</h3>
        <div id="appvue"></div>
    </div>
    
    <!-- React Example -->
    <div class="container">
        <h3>PHP and REACT - NO Build step</h3>
        <div id="appreact"></div>
    </div>

<?php
$this->preactHello();
$this->preactCounter();
$this->vueHello();
$this->reactHello();
?>
```

**Preact Component with HTM (JSX alternative):**
```php
private function preactHello()
{
?>
    <script type="module">
        import { render } from 'preact';
        import { html } from 'htm/preact';

        function Button({ action, children }) {
            return html`<button onClick=${action}>${children}</button>`;
        }

        function hey() {
            return html`<h2>Hello from Preact!</h2>`;
        }

        export function App() {
            return html`<${hey} />`;
        }

        render(html`<${App} />`, document.getElementById('app'));
    </script>
<?php
}
```

**Preact Counter with Hooks:**
```php
private function preactCounter()
{
?>
    <script type="module">
        import { render } from 'preact';
        import { useState } from 'preact/hooks';
        import { html } from 'htm/preact';

        function Button({ action, children }) {
            return html`<button onClick=${action}>${children}</button>`;
        }

        function Counter() {
            const [count, setCount] = useState(0);

            return html`
                <div class="counter-container">
                    <${Button} action=${() => setCount(count + 1)}>Increment<//>
                    <input readonly value=${count} />
                    <${Button} action=${() => setCount(count - 1)}>Decrement<//>
                </div>
            `;
        }

        render(html`<${Counter} />`, document.getElementById('counter'));
    </script>
<?php
}
```

**Vue Component:**
```php
private function vueHello()
{
?>
    <script>
        const { createApp } = Vue;

        const app = createApp({
            data() {
                return {
                    message: 'Hello from Vue.js!'
                }
            },
            template: `<h2>{{ message }}</h2>`
        });

        app.mount('#appvue');
    </script>
<?php
}
```

**React with ES Modules:**
```php
private function reactHello()
{
?>
    <script type="module">
        import React from "https://unpkg.com/es-react@latest/dev/react.js";
        import ReactDOM from "https://unpkg.com/es-react@latest/dev/react-dom.js";
        import htm from "https://unpkg.com/htm@latest?module";
        const html = htm.bind(React.createElement);

        const App = (props) => {
            return html`<div><h2>Hello from React! foo: ${props.foo}</h2></div>`;
        };

        ReactDOM.render(
            html`<${App} foo=${"bar"} />`,
            document.getElementById("appreact")
        );
    </script>
<?php
}
```

**Controller.php** - Clean and simple:
```php
public function display($reqRoute, $reqMet)
{
    $this->index($reqRoute, $reqMet);
}

public function index($reqRoute, $reqMet) 
{
    $model = new Model(); 
    $view = new View();

    switch ($reqRoute) {
        case '/reactnb':
            $users = $model->getAllUsers($this->table);
            $data = ['users' => $users, 'view'=> 'index'];
            return $view->render($data); 
            break;
    }
}
```

### Key Technologies

1. **Import Maps**: Modern way to manage ES module imports
   ```javascript
   <script type="importmap">
   {
     "imports": {
       "preact": "https://esm.sh/preact@10.23.1"
     }
   }
   </script>
   ```

2. **HTM (Hyperscript Tagged Markup)**: JSX alternative without build
   ```javascript
   import { html } from 'htm/preact';
   return html`<div>${props.name}</div>`;
   ```

3. **ESM.sh**: CDN that serves npm packages as ES modules
   ```javascript
   import { Button } from "https://esm.sh/@mui/material";
   ```

### Characteristics

**Pros:**
- ✅ No build step at all
- ✅ Modern JSX-like syntax with HTM
- ✅ Can use npm packages via esm.sh
- ✅ Multiple frameworks on one page
- ✅ ES6+ features natively
- ✅ Hot reload in browser (no webpack)

**Cons:**
- ⚠️ Requires modern browser support
- ⚠️ CDN dependency
- ⚠️ Slightly slower than bundled (for large apps)
- ⚠️ Import maps support varies

**Use When:**
- Rapid prototyping
- Learning multiple frameworks
- Small to medium applications
- Want modern JS without tooling overhead
- Comparing React/Vue/Preact

---

## 🎯 Decision Matrix

### Choose Pattern 1 (CDN Components) if:
- ✅ Adding simple interactivity to PHP pages
- ✅ No build tools wanted
- ✅ Small components (buttons, forms)
- ✅ Quick prototyping

### Choose Pattern 2 (Built App - Embedded) if:
- ✅ Need React SPA in **part** of page
- ✅ Want full React ecosystem
- ✅ Dashboard widgets
- ✅ Mix PHP and React UI

### Choose Pattern 3 (Built App - Full Page) if:
- ✅ Building complete SPA
- ✅ Complex CRUD application
- ✅ Client-side routing needed
- ✅ PHP is just API backend

### Choose Pattern 4 (ES Modules - No Build) if:
- ✅ Want modern JS WITHOUT build step
- ✅ Comparing multiple frameworks
- ✅ Rapid development
- ✅ Medium complexity apps
- ✅ Hate webpack/babel/npm scripts

### Choose Pattern 5 (ES Modules + HMR) if:
- ✅ Active development workflow
- ✅ Want instant feedback (auto-reload)
- ✅ No build tools wanted
- ✅ Component development
- ✅ Need fastest iteration speed

---

## 🔷 Pattern 5: ES Modules + HMR (`src/Modules/Reacthmr`)

### Philosophy: "Edit → Save → See Changes Instantly"
Combines ES Modules (Pattern 4) with Hot Module Reload using Server-Sent Events. **No webpack, no Vite** - just PHP watching files and browser auto-reloading.

### How It Works

```
┌──────────────┐
│   Browser    │
│ EventSource  │◄──────── SSE Stream
│  /hmr        │
└──────────────┘
                        ┌─────────────┐
                        │     PHP     │
                        │  watches:   │
                        │  *.php      │
                        │  *.js       │
                        │  *.html     │
                        └─────────────┘
```

1. Browser connects to SSE endpoint
2. PHP watches files for changes
3. File modified → PHP sends `reload` event
4. Browser receives event → auto-reloads

**Reload time: ~1.5 seconds**

### Implementation

**Controller.php** - File Watching + SSE Stream:
```php
private function hmrStream()
{
    // Set SSE headers
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    
    // Store initial file hashes
    $fileHashes = $this->getFileHashes();
    
    while (true) {
        // Check for changes every second
        $currentHashes = $this->getFileHashes();
        
        if ($currentHashes !== $fileHashes) {
            // Send reload event
            echo "event: reload\n";
            echo "data: " . json_encode([
                'timestamp' => time(),
                'message' => 'Files changed - reloading...'
            ]) . "\n\n";
            flush();
            
            $fileHashes = $currentHashes;
        }
        
        sleep(1);
    }
}

private function getFileHashes(): string
{
    $hash = '';
    foreach ($this->watchPaths as $path) {
        $fullPath = THIS_DIR . '/' . $path;
        if (is_file($fullPath)) {
            $hash .= filemtime($fullPath);
        }
    }
    return md5($hash);
}
```

**View.php** - HMR Client:
```php
<script>
// HMR Client using Server-Sent Events
(function() {
    const eventSource = new EventSource('<?php echo BASE_URL; ?>/reacthmr/hmr');
    
    eventSource.addEventListener('reload', (e) => {
        console.log('[HMR] Reloading...');
        
        // Smooth fade
        document.body.style.opacity = '0.5';
        
        // Reload after 300ms
        setTimeout(() => location.reload(), 300);
    });
    
    eventSource.onerror = () => {
        console.log('[HMR] Reconnecting...');
        eventSource.close();
        setTimeout(connect, 2000); // Reconnect
    };
})();
</script>
```

**Preact Component with HTM:**
```php
<script type="module">
    import { render } from 'preact';
    import { useState } from 'preact/hooks';
    import { html } from 'htm/preact';

    function TodoApp() {
        const [todos, setTodos] = useState([]);
        const [input, setInput] = useState('');

        const addTodo = () => {
            setTodos([...todos, { id: Date.now(), text: input }]);
            setInput('');
        };

        return html`
            <div>
                <input value=${input} onInput=${e => setInput(e.target.value)} />
                <button onClick=${addTodo}>Add Todo</button>
                
                ${todos.map(todo => html`
                    <div key=${todo.id}>${todo.text}</div>
                `)}
            </div>
        `;
    }

    render(html`<${TodoApp} />`, document.getElementById('app'));
</script>
```

### Features

**1. Real-Time Status Indicator:**
```html
<div id="hmr-status" class="hmr-status">
    <span class="status-dot connected"></span>
    <span class="status-text">HMR Connected</span>
</div>
```

**States:**
- 🟢 Connected - HMR active
- 🟠 Reconnecting - Connection lost
- 🔵 Reloading - Changes detected

**2. Configurable Watch Paths:**
```php
private $watchPaths = [
    'modules/reacthmr/templates/',
    'modules/reacthmr/components/',
    'modules/reacthmr/View.php',
    'modules/reacthmr/Controller.php'
];
```

**3. PHP → JS Data Flow:**
```php
<!-- PHP Data as JSON -->
<script type="application/json" id="php-data">
    <?php echo json_encode($data, JSON_PRETTY_PRINT); ?>
</script>

<script type="module">
    const phpData = JSON.parse(
        document.getElementById('php-data').textContent
    );
    
    function UserTable() {
        return html`
            <table>
                ${phpData.users.map(user => html`
                    <tr><td>${user.name}</td></tr>
                `)}
            </table>
        `;
    }
</script>
```

### Workflow

```bash
# 1. Visit page
/reacthmr

# 2. Open file in editor
modules/reacthmr/View.php

# 3. Make changes
- Edit component code
- Change styles
- Modify text

# 4. Save file
Ctrl+S

# 5. Watch browser auto-reload! ✨
Total time: ~1.5 seconds
```

### Characteristics

**Pros:**
- ✅ Instant feedback on changes
- ✅ No build step required
- ✅ Simple PHP implementation
- ✅ Works with any ES module framework
- ✅ Auto-reconnect on disconnect
- ✅ Smooth reload with fade effect
- ✅ Real-time connection status
- ✅ Fastest development iteration

**Cons:**
- ⚠️ ~1.5s reload (vs instant HMR in webpack)
- ⚠️ Full page reload (not hot swap)
- ⚠️ File watching overhead
- ⚠️ Must disable in production
- ⚠️ SSE connection may timeout on some hosts

**Use When:**
- Active component development
- Rapid prototyping
- UI/style tweaking
- Learning ES modules
- Want fastest iteration without build tools

**Don't Use When:**
- Production environment (always disable)
- Shared hosting (SSE limitations)
- Very large projects (file watching overhead)

### Performance

| Event | Time |
|-------|------|
| File saved | 0ms |
| PHP detects | ~1000ms |
| SSE sent | ~50ms |
| Browser fade | 300ms |
| Page reload | ~200ms |
| **Total** | **~1.5s** |

**vs Webpack HMR:**
- Webpack: ~500ms (hot swap, no reload)
- ReactHMR: ~1.5s (full reload)
- Trade-off: Simplicity vs Speed

### Configuration

**Faster Detection:**
```php
sleep(0.5); // Check every 500ms instead of 1s
```

**Watch More Files:**
```php
private $watchPaths = [
    'modules/reacthmr/',
    'modules/mymodule/',
    'common/Assets/',
    'src/Etc/Config.php'
];
```

**Instant Reload (No Fade):**
```javascript
setTimeout(() => location.reload(), 0);
```

### Production Safety

**Auto-disable in production:**
```php
<?php if (ENVIRONMENT === 'development'): ?>
    <?php $this->hmrClient(); ?>
<?php endif; ?>
```

---

## 🔧 Workflow Patterns

### Pattern 1 Workflow (CDN Components)
```bash
# No build needed!
1. Create component.js
2. Load React from CDN
3. Write vanilla React (no JSX)
4. Serve via PHP route
```

### Pattern 2 & 3 Workflow (Built Apps)
```bash
# Separate React development
1. cd modules/reactb/etc/react-app
2. npm install
3. npm start              # Develop at localhost:3000
4. npm run build          # Build to etc/build/
5. Update View.php paths  # Copy from build/index.html
6. Create asset routes    # Controller serves build files
7. Test at /reactb
```

### Pattern 4 Workflow (ES Modules)
```bash
# No build, pure development
1. Write components inline in View.php
2. Use import maps for dependencies
3. Write HTM templates (JSX-like)
4. Refresh browser to see changes
5. No webpack, no babel, no npm build
```

### Pattern 5 Workflow (ES Modules + HMR)
```bash
# No build, auto-reload on save
1. Visit /reacthmr
2. Open View.php in editor
3. Edit components/styles/text
4. Save file (Ctrl+S)
5. Watch browser auto-reload (~1.5s)
6. Repeat!
```

---

## 🌐 CDN Providers for Pattern 4

### esm.sh (Recommended)
```javascript
import React from "https://esm.sh/react@18";
import { Button } from "https://esm.sh/@mui/material@5";
```

### unpkg
```javascript
import React from "https://unpkg.com/es-react@latest/dev/react.js";
```

### jsDelivr
```javascript
import Vue from "https://cdn.jsdelivr.net/npm/vue@3/dist/vue.esm-browser.js";
```

---

## 📦 Asset Management Strategies

### Pattern 1: Direct Serve
```php
case "/comp":
    require_once THIS_DIR . "/Modules/react/etc/component.js";
    break;
```

### Pattern 2 & 3: Route Per Asset
```php
public function mainjs()
{
    require_once THIS_DIR . "/Modules/reactb/etc/build/static/js/main.10d2eb17.js";
}

// routes/Routes.php
$router->addRoute("/reactb/mainjs", Controller::class, "mainjs");
```

### Pattern 4: CDN Only (No PHP Asset Routes)
```html
<script type="module">
    import { render } from 'preact';  // Direct CDN import
</script>
```

---

## 🎨 Integration Philosophy

All four patterns follow upMVC's core principle:

> **"Show different approaches, let developers choose"**

### Pattern Summary

| Aspect | Pattern 1 | Pattern 2 | Pattern 3 | Pattern 4 |
|--------|-----------|-----------|-----------|-----------|
| **Build Step** | No | Yes | Yes | No |
| **JSX Support** | No | Yes | Yes | HTM (JSX-like) |
| **Complexity** | Low | Medium | High | Medium |
| **PHP Integration** | Tight | Mixed | Loose | Tight |
| **Best For** | Widgets | Sections | Full SPA | Modern Rapid Dev |

---

## 🚀 Getting Started

### Try Pattern 1 (Easiest)
```bash
# Visit: /react
# See: Simple Like button with React from CDN
```

### Try Pattern 2
```bash
# Visit: /reactb
# See: React app embedded in PHP columns
```

### Try Pattern 3
```bash
# Visit: /crud
# See: Full React CRUD SPA
```

### Try Pattern 4 (Most Modern)
```bash
# Visit: /reactnb
# See: React, Vue, Preact side-by-side, no build!
```

### Try Pattern 5 (Development)
```bash
# Visit: /reacthmr
# Edit: modules/reacthmr/View.php
# Save: Ctrl+S
# Watch: Browser auto-reloads! ✨
```

---

## 🔗 Related Documentation

- **[Module Philosophy](MODULE_PHILOSOPHY.md)** - Why modules are reference implementations
- **[Routing Strategies](routing/ROUTING_STRATEGIES.md)** - How routes work
- **[Pure PHP Philosophy](PHILOSOPHY_PURE_PHP.md)** - upMVC design principles

---

## 💡 Key Takeaways

1. **Five Valid Approaches**
   - CDN Components (no build)
   - Built React - Embedded (build once)
   - Built React - Full SPA (build once)
   - ES Modules (no build, modern)
   - ES Modules + HMR (no build, auto-reload)

2. **No "Right" Way**
   - Choose based on project needs
   - Can mix patterns in same app
   - All are production-ready

3. **Build vs No Build**
   - Build = Full ecosystem, optimized
   - No Build = Faster development, simpler
   - Pattern 4/5 = Best of both worlds

4. **HMR Without Webpack**
   - Pattern 5 shows PHP can do HMR
   - Server-Sent Events + file watching
   - ~1.5s reload vs manual refresh

5. **Reference Implementations**
   - Study all four patterns
   - Delete what you don't need
   - Build your own variation

---

## 🎓 Learning Path

### Beginner
1. Start with **Pattern 1** (CDN Components)
2. Add simple interactive widgets
3. Learn React basics without tooling

### Intermediate
1. Try **Pattern 4** (ES Modules)
2. Use HTM for JSX-like syntax
3. Compare React/Vue/Preact

### Advanced
1. Use **Pattern 2** (Embedded Build)
2. Build complex React components
3. Mix with PHP backend

### Expert
1. Use **Pattern 3** (Full SPA)
2. Separate frontend/backend
3. React Router, state management

---

**Remember:** These are **reference implementations**, not requirements. Study them, learn from them, then **delete them and build your own way**.

That's the upMVC philosophy. 🚀
