# ReactHMR Module - Hot Module Reload Without Build

> **🔥 Hot Module Reload for ES Modules using Server-Sent Events**  
> Edit → Save → Watch Browser Auto-Reload. Zero build tools required.

---

## 🎯 What is This?

This module demonstrates **Hot Module Reload (HMR)** for ES Modules using PHP and Server-Sent Events (SSE). When you edit files, the browser automatically refreshes - **no webpack, no Vite, no build step**.

### **The Magic:**
- PHP watches files for changes
- Sends SSE event when file modified
- Browser receives event and reloads
- Total reload time: ~300ms

---

## 🚀 Quick Start

### 1. Visit the Demo
```
http://localhost/reacthmr
```

### 2. Open File in Editor
```
Modules/Reacthmr/View.php
```

### 3. Make a Change
- Edit any text
- Change component code
- Modify styles

### 4. Save File

### 5. Watch Browser Auto-Reload! ✨

---

## 📊 Features

### ✅ What This Module Shows

1. **Hot Module Reload**
   - File watching with PHP
   - Server-Sent Events (SSE) streaming
   - Automatic browser refresh

2. **ES Modules Pattern (Pattern 4)**
   - Import Maps for dependencies
   - HTM for JSX-like syntax
   - Preact + Vue.js examples
   - Zero build required

3. **PHP → JS Data Flow**
   - Pass data from PHP to components
   - JSON serialization
   - Component hydration

4. **Multiple Frameworks**
   - Preact components
   - Vue.js components
   - All on same page with HMR

5. **Real-Time Status Indicator**
   - Connection status (connected/disconnected)
   - Reload notifications
   - Auto-reconnect on disconnect

---

## 🏗 Architecture

### How HMR Works

```
┌──────────────┐
│   Browser    │
│              │
│  EventSource │◄─────────┐
│  /reacthmr/hmr│          │
└──────────────┘          │
                          │ SSE Stream
                          │
                    ┌─────┴──────┐
                    │    PHP     │
                    │ Controller │
                    │            │
                    │ watches:   │
                    │ *.php      │
                    │ *.js       │
                    │ *.html     │
                    └────────────┘
```

### The Flow

1. **Browser connects** to `/reacthmr/hmr` (SSE endpoint)
2. **PHP Controller** starts watching files
3. **Files change** - PHP detects modification
4. **SSE event sent** - `reload` event to browser
5. **Browser reloads** - Smooth fade + refresh

---

## 📁 File Structure

```
reacthmr/
├── Controller.php          ← HMR logic, file watching, SSE stream
├── Model.php              ← Sample data (users, stats)
├── View.php               ← Main UI, Import Maps, components
├── routes/
│   └── Routes.php         ← Route registration
└── components/
    └── TodoApp.js         ← External component (auto-reloads)
```

---

## 🔧 How It Works

### 1. Controller - File Watching

**`Controller.php`** implements file watching:

```php
private function hmrStream()
{
    // Set SSE headers
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    
    // Store initial file hashes
    $fileHashes = $this->getFileHashes();
    
    while (true) {
        // Check for changes
        $currentHashes = $this->getFileHashes();
        
        if ($currentHashes !== $fileHashes) {
            // Send reload event
            echo "event: reload\n";
            echo "data: " . json_encode(['timestamp' => time()]) . "\n\n";
            flush();
            
            $fileHashes = $currentHashes;
        }
        
        sleep(1); // Check every second
    }
}
```

**Watched Paths:**
```php
private $watchPaths = [
    'Modules/Reacthmr/templates/',
    'Modules/Reacthmr/components/',
    'Modules/Reacthmr/View.php',
    'Modules/Reacthmr/Controller.php'
];
```

### 2. Client - SSE Connection

**HMR Client** in `View.php`:

```javascript
const eventSource = new EventSource('/reacthmr/hmr');

eventSource.addEventListener('reload', (e) => {
    console.log('[HMR] Reloading...');
    
    // Smooth fade
    document.body.style.opacity = '0.5';
    
    // Reload after 300ms
    setTimeout(() => location.reload(), 300);
});

eventSource.onerror = () => {
    // Reconnect on error
    setTimeout(connect, 2000);
};
```

### 3. Status Indicator

Real-time connection status:

```html
<div id="hmr-status" class="hmr-status">
    <span class="status-dot connected"></span>
    <span class="status-text">HMR Connected</span>
</div>
```

**Status States:**
- 🟢 **Connected** - HMR active
- 🟠 **Disconnected** - Reconnecting...
- 🔵 **Reloading** - Changes detected
- 🔴 **Error** - SSE not supported

---

## 🎨 Components Included

### 1. Counter (Inline)
Simple counter with increment/decrement.

**Location:** `View.php` → `preactCounter()`

```javascript
function Counter() {
    const [count, setCount] = useState(0);
    return html`
        <button onClick=${() => setCount(count + 1)}>
            Increment
        </button>
    `;
}
```

### 2. User Table (PHP Data)
Table showing users from PHP.

**Location:** `View.php` → `preactUsers()`

**PHP → JS:**
```php
<script type="application/json" id="php-data">
    <?php echo json_encode($data); ?>
</script>
```

```javascript
const phpData = JSON.parse(
    document.getElementById('php-data').textContent
);
```

### 3. Todo App (External File)
Full todo app loaded from separate file.

**Location:** `components/TodoApp.js`

**Try editing this file and watch HMR reload!**

### 4. Stats Dashboard (PHP Data)
Statistics cards from PHP backend.

### 5. Vue.js Example
Shows HMR works with any framework.

---

## ⚡ Performance

### Reload Speed

| Event | Time |
|-------|------|
| File saved | 0ms |
| PHP detects change | ~1000ms (sleep interval) |
| SSE event sent | ~50ms |
| Browser fade | 300ms |
| Page reload | ~200ms |
| **Total** | **~1550ms** |

### Optimization Options

**Faster Detection (500ms):**
```php
sleep(0.5); // Check every 500ms instead of 1s
```

**Instant Reload (no fade):**
```javascript
setTimeout(() => location.reload(), 0);
```

**Watch Specific Files Only:**
```php
private $watchPaths = [
    'Modules/Reacthmr/View.php' // Only watch View
];
```

---

## 🔧 Configuration

### Change Watched Paths

Edit `Controller.php`:

```php
private $watchPaths = [
    'Modules/Reacthmr/',           // Entire module
    'common/Assets/',              // Common assets
    'etc/Config.php',              // Config file
    'modules/mymodule/View.php'    // Specific file
];
```

### Change Check Interval

```php
sleep(1); // Default: check every 1 second

// Faster (more CPU):
sleep(0.5); // Check every 500ms

// Slower (less CPU):
sleep(2); // Check every 2 seconds
```

### File Type Filters

Edit `getFilesRecursive()`:

```php
// Only watch specific extensions
if (in_array($ext, ['php', 'js', 'html', 'css'])) {
    $files[] = $file->getPathname();
}

// Add more types:
if (in_array($ext, ['php', 'js', 'html', 'css', 'json', 'xml'])) {
    $files[] = $file->getPathname();
}
```

---

## 🎓 Use Cases

### Development Workflow
- Rapid prototyping
- Component development
- Style tweaking
- Debug faster

### When to Use HMR
✅ Development environment  
✅ Local testing  
✅ Component libraries  
✅ UI experimentation

### When NOT to Use HMR
❌ Production (disabled automatically)  
❌ Shared hosting (SSE may timeout)  
❌ Very large projects (file watching overhead)

---

## 🆚 Comparison

### vs Webpack HMR

| Feature | ReactHMR (This) | Webpack HMR |
|---------|-----------------|-------------|
| **Build Step** | ❌ None | ✅ Required |
| **Setup Time** | 2 minutes | 30+ minutes |
| **Reload Speed** | ~1.5s | ~500ms |
| **CPU Usage** | Low | High |
| **Complexity** | Simple | Complex |
| **Dependencies** | None | Many |

### vs Vite HMR

| Feature | ReactHMR (This) | Vite |
|---------|-----------------|------|
| **Build Step** | ❌ None | Dev server |
| **Setup** | Drop in module | npm install |
| **Speed** | Good | Excellent |
| **PHP Integration** | Native | Requires proxy |

---

## 🔒 Production Considerations

### Disable HMR in Production

**Option 1: Environment Check**
```php
// In View.php
<?php if (ENVIRONMENT === 'development'): ?>
    <?php $this->hmrClient(); ?>
<?php endif; ?>
```

**Option 2: Route Protection**
```php
// In Controller.php
private function hmrStream()
{
    if (ENVIRONMENT !== 'development') {
        http_response_code(404);
        exit;
    }
    
    // ... HMR code
}
```

### Security

HMR exposes file paths and modification times. **Always disable in production.**

---

## 🐛 Troubleshooting

### HMR Not Connecting

**Check:**
1. Browser supports EventSource (all modern browsers)
2. Route `/reacthmr/hmr` is registered
3. No firewall blocking SSE
4. PHP not timing out (set `max_execution_time`)

**Fix PHP Timeout:**
```php
set_time_limit(0); // No timeout
```

### Changes Not Detected

**Check:**
1. File is in `watchPaths`
2. File extension is monitored (php, js, html, css)
3. File permissions (PHP can read file)

**Debug:**
```php
// Add logging
error_log("Current hash: $currentHashes");
error_log("Previous hash: $fileHashes");
```

### Slow Reloads

**Reduce check interval:**
```php
sleep(0.5); // Instead of sleep(1)
```

**Watch fewer files:**
```php
private $watchPaths = [
    'Modules/Reacthmr/View.php' // Only this file
];
```

### Connection Drops

**Increase timeout:**
```php
$timeout = 60; // 60 seconds instead of 30
```

**Add heartbeat:**
```php
echo ": heartbeat\n\n";
flush();
```

---

## 🚀 Extending HMR

### Watch Other Modules

```php
private $watchPaths = [
    'Modules/Reacthmr/',
    'modules/reactnb/',      // Watch another module
    'modules/mymodule/'      // Watch your module
];
```

### Multiple HMR Streams

Create module-specific HMR:

```php
// /mymodule/hmr
$router->addRoute("/mymodule/hmr", MyModuleController::class, "hmrStream");
```

### Custom Events

Send different event types:

```php
// PHP Controller
echo "event: css-update\n";
echo "data: {}\n\n";

// JS Client
eventSource.addEventListener('css-update', (e) => {
    // Reload only CSS, not full page
    document.querySelectorAll('link[rel="stylesheet"]').forEach(link => {
        link.href = link.href + '?t=' + Date.now();
    });
});
```

---

## 📚 Learn More

### Related Patterns

- **[Pattern 4 - ES Modules](../reactnb/)** - Base pattern this builds on
- **[React Integration](../../docs/REACT_INTEGRATION_PATTERNS.md)** - All React patterns
- **[Module Philosophy](../../docs/MODULE_PHILOSOPHY.md)** - Reference implementation concept

### Technologies Used

- **Server-Sent Events (SSE)** - Real-time push from server
- **Import Maps** - ES module dependency management
- **HTM** - JSX alternative without build
- **Preact** - Lightweight React alternative (3KB)
- **PHP File Watching** - `filemtime()` + hashing

---

## 🎯 Key Takeaways

1. **HMR Without Build Tools**
   - PHP can watch files
   - SSE enables push notifications
   - Browser reloads automatically

2. **Development Speed**
   - Edit → Save → See changes (~1.5s)
   - No webpack configuration
   - No npm build step

3. **Production Ready**
   - Easily disabled in production
   - Low overhead in development
   - Works with any ES module framework

4. **Reference Implementation**
   - Study the code
   - Adapt to your needs
   - Delete if not needed

---

## 💡 Pro Tips

### Tip 1: Component-Only Reload
Instead of full page reload, update just the component:

```javascript
eventSource.addEventListener('reload', (e) => {
    // Re-render component instead of reload
    render(html`<${App} />`, document.getElementById('app'));
});
```

### Tip 2: Preserve State
Store state in localStorage before reload:

```javascript
// Before reload
localStorage.setItem('app-state', JSON.stringify(state));

// After reload
const savedState = JSON.parse(localStorage.getItem('app-state'));
```

### Tip 3: Watch Config Changes
Watch configuration files:

```php
private $watchPaths = [
    'Modules/Reacthmr/',
    'etc/Config.php',  // Config changes trigger reload
];
```

---

## 🎉 Conclusion

**ReactHMR demonstrates that you DON'T need complex build tools for modern development.**

- No webpack
- No Vite
- No complex configuration
- Just PHP + SSE + ES Modules

**Edit. Save. See changes instantly.** ✨

---

**This is a reference implementation. Study it, adapt it, or delete it. Build your way.** 🚀
