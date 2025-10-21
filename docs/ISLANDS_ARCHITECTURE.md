# Islands Architecture Pattern

## Overview

The **Islands Architecture** is a modern web development pattern that combines server-side rendering (SSR) with selective client-side interactivity. In upMVC, we use **PHP for the core system** and **React/Preact "islands"** for interactive components - all **without a build step**.

## Table of Contents

- [What Are Islands?](#what-are-islands)
- [Why This Architecture?](#why-this-architecture)
- [Architecture Diagram](#architecture-diagram)
- [Benefits](#benefits)
- [Trade-offs](#trade-offs)
- [When to Use Islands](#when-to-use-islands)
- [Implementation Patterns](#implementation-patterns)
- [Best Practices](#best-practices)
- [Real-World Examples](#real-world-examples)

---

## What Are Islands?

**Islands** are isolated, interactive components that exist within a server-rendered HTML page.

```
┌─────────────────────────────────────┐
│  PHP Rendered Page (Static HTML)    │
│                                      │
│  ┌──────────────┐                   │
│  │ React Island │  ← Interactive    │
│  │  (Counter)   │                   │
│  └──────────────┘                   │
│                                      │
│  Static Content...                  │
│                                      │
│  ┌──────────────┐  ┌──────────────┐│
│  │ React Island │  │ React Island ││
│  │  (Todo App)  │  │   (Chart)    ││
│  └──────────────┘  └──────────────┘│
│                                      │
│  More Static Content...             │
└─────────────────────────────────────┘
```

**Key Concept:** JavaScript only loads and executes for the interactive parts!

---

## Why This Architecture?

### Traditional Approaches:

#### 1. **Pure PHP** (Old Way)
```php
// Everything is server-side
// No interactivity without page reload
❌ Poor UX for dynamic features
✅ Simple, fast initial load
```

#### 2. **Full SPA** (React/Vue/Angular)
```javascript
// Everything is JavaScript
// Page shell loads, then JS hydrates
❌ Large bundle size
❌ Complex build process
❌ Poor SEO without SSR
❌ Slow initial load
✅ Rich interactivity
```

#### 3. **Islands Architecture** (Modern Way)
```php
// PHP renders HTML + selective React islands
✅ Fast initial load
✅ Rich interactivity where needed
✅ Great SEO
✅ Simple architecture
✅ No build step (in our case!)
```

---

## Architecture Diagram

### Request Flow

```
┌─────────┐
│ Browser │
└────┬────┘
     │ 1. GET /dashboard
     ▼
┌─────────────┐
│   Router    │
│ (Routes.php)│
└─────┬───────┘
      │ 2. Route to Controller
      ▼
┌──────────────┐
│  Controller  │◄──┐
│              │   │ 3. Get Data
│              │   │
└──────┬───────┘   │
       │           │
       │      ┌────┴────┐
       │      │  Model  │
       │      └─────────┘
       │ 4. Pass data to View
       ▼
┌──────────────────────────────────┐
│           View.php               │
│                                  │
│  <?php                           │
│    // Server renders HTML        │
│    <div id="static">             │
│      <?= $data['content'] ?>     │
│    </div>                        │
│                                  │
│    // Island placeholder         │
│    <div id="island-app"></div>   │
│  ?>                              │
│                                  │
│  <script type="module">          │
│    // Client-side island         │
│    import { render } from 'cdn'; │
│    render(Component, '#island'); │
│  </script>                       │
└──────────────────────────────────┘
       │ 5. Return HTML
       ▼
┌─────────┐
│ Browser │ 6. Parse HTML
│         │ 7. Execute island JS
└─────────┘
```

### Data Flow

```
SERVER SIDE (PHP)                   CLIENT SIDE (Browser)
═══════════════════                 ════════════════════

┌─────────────┐
│   Model     │
│  Database   │
└──────┬──────┘
       │ Query data
       ▼
┌─────────────┐                     
│ Controller  │
│ $data = []  │
└──────┬──────┘
       │ Pass data
       ▼
┌─────────────┐                     ┌──────────────────┐
│    View     │──── HTML ───────────>│ Browser renders  │
│  render()   │                     │  static content  │
└─────────────┘                     └────────┬─────────┘
       │                                     │
       │ Embed data as JSON                  │
       ▼                                     ▼
<script id="data">                   ┌──────────────────┐
  <?= json_encode($data) ?>          │  React Island    │
</script>                            │  reads JSON      │
                                     │  + hydrates      │
                                     └──────────────────┘
```

---

## Benefits

### 1. **Performance**

| Metric | Full SPA | Islands Architecture |
|--------|----------|---------------------|
| **Initial Load** | 2-5s (JS bundle) | 0.5-1s (HTML) |
| **Time to Interactive** | 2-5s | 0.5-1s (static) + instant islands |
| **Bundle Size** | 200KB-2MB | 10-50KB per island |
| **Server Response** | 200KB HTML shell | Full HTML content |

**Example:**
```
Traditional SPA:
├─ Load React (50KB)
├─ Load App Code (200KB)
├─ Parse + Execute (2s)
└─ Render (500ms)
= 2.5s to see content

Islands:
├─ PHP renders HTML (100ms)
├─ Browser shows content (immediate)
├─ Load island JS (20KB, parallel)
└─ Island interactive (500ms)
= Content visible in 100ms!
```

### 2. **SEO**

```html
<!-- What search engines see -->
<h1>Product Name</h1>
<p>Full description here...</p>
<div class="reviews">
  <!-- All content already rendered by PHP -->
  <div class="review">Review 1...</div>
  <div class="review">Review 2...</div>
</div>

<!-- Island adds interactivity later -->
<div id="review-filter"></div>
```

✅ **Perfect for:**
- E-commerce product pages
- Blog posts
- Landing pages
- Documentation

### 3. **Developer Experience**

```bash
# Traditional React
npm install              # 5 minutes
npm run build           # 30 seconds every change
npm run dev             # Background process
# Setup: webpack, babel, eslint, etc.

# Islands (upMVC)
composer install        # 1 minute
# Edit file
# Save
# Refresh browser
# Done! ✨
```

**No build step means:**
- ✅ Faster onboarding
- ✅ Simpler debugging
- ✅ No node_modules bloat
- ✅ Deploy = upload files
- ✅ Junior dev friendly

### 4. **Progressive Enhancement**

```php
<!-- Works without JavaScript -->
<form method="POST" action="/api/todos">
  <input name="title" required>
  <button type="submit">Add</button>
</form>

<!-- Enhanced with JavaScript -->
<script type="module">
  // Island adds real-time validation,
  // optimistic updates, etc.
</script>
```

**Result:** Your app works even if JS fails to load!

### 5. **Selective Complexity**

```
Simple Page (Blog):
└─ 100% PHP, 0% JS
   = Simple, fast, maintainable

Interactive Page (Dashboard):
├─ 70% PHP (layout, navigation)
└─ 30% React Islands (charts, tables)
   = Interactive where needed

Admin CRUD:
├─ 50% PHP (forms, validation)
└─ 50% React Islands (rich editor, dropzone)
   = Balanced approach
```

---

## Trade-offs

### ✅ **Advantages**

| Benefit | Description |
|---------|-------------|
| **Fast Initial Load** | Server-rendered HTML arrives quickly |
| **Great SEO** | Search engines see full content |
| **Progressive Enhancement** | Works without JavaScript |
| **Simple Deployment** | No build step needed |
| **Lower Complexity** | Use JS only where needed |
| **Better Performance** | Smaller bundle sizes |

### ⚠️ **Challenges**

| Challenge | Impact | Solution |
|-----------|--------|----------|
| **State Management** | Sharing state between islands | Use localStorage, URL params, or simple event bus |
| **Code Duplication** | Similar logic in PHP and JS | Accept some duplication, or use API |
| **Hydration** | Initial data from PHP → JS | Embed JSON in HTML |
| **Routing** | Page transitions | Use PHP routing + Turbo/HTMX for SPA feel |
| **Dev Tools** | Less tooling than full React | Browser DevTools + error boundaries |

---

## When to Use Islands

### ✅ **Perfect Use Cases**

#### 1. **Interactive Widgets**
```php
// Static content
<div class="article">
  <?= $article->content ?>
</div>

// Interactive comments island
<div id="comments-app"></div>
```

**Examples:**
- Todo lists
- Shopping carts
- Comment sections
- Live search
- Filters/sorting

#### 2. **Data Visualization**
```php
// Static stats
<div class="summary">
  Total: <?= $stats['total'] ?>
</div>

// Interactive chart island
<div id="chart-app"></div>
```

**Examples:**
- Charts and graphs
- Real-time dashboards
- Analytics displays
- Progress trackers

#### 3. **Rich Forms**
```php
// Basic form (works without JS)
<form method="POST">
  <input name="email">
  <button>Submit</button>
</form>

// Enhanced with island
<script>
  // Add: real-time validation,
  // autocomplete, drag-drop, etc.
</script>
```

**Examples:**
- File uploaders
- Rich text editors
- Multi-step forms
- Form builders

#### 4. **Real-time Features**
```php
// Static message history
<div class="messages">
  <?php foreach($messages as $msg): ?>
    <div><?= $msg->text ?></div>
  <?php endforeach; ?>
</div>

// Live updates island
<div id="live-chat-app"></div>
```

**Examples:**
- Chat/messaging
- Notifications
- Live feeds
- Collaborative editing

### ❌ **Not Ideal For**

| Scenario | Better Approach |
|----------|----------------|
| **Fully Static Site** | Pure PHP (no islands needed) |
| **Complex SPA** | Full React app might be simpler |
| **High Interactivity** | If 90%+ needs JS, consider SPA |
| **Mobile App** | Native or React Native |

---

## Implementation Patterns

### Pattern 1: **Inline Island** (Simplest)

```php
<?php
// modules/dashboard/View.php
class View {
  public function render($data) {
    ?>
    <div id="counter-app"></div>
    
    <script type="module">
      import { render } from 'preact';
      import { useState } from 'preact/hooks';
      import { html } from 'htm/preact';
      
      function Counter() {
        const [count, setCount] = useState(0);
        return html`
          <button onClick=${() => setCount(count + 1)}>
            Count: ${count}
          </button>
        `;
      }
      
      render(html`<${Counter} />`, 
             document.getElementById('counter-app'));
    </script>
    <?php
  }
}
```

**Pros:**
- ✅ Simple, all in one file
- ✅ No external dependencies
- ✅ Easy to understand

**Cons:**
- ❌ Not reusable
- ❌ Grows large quickly

### Pattern 2: **External Component** (Reusable)

```php
<?php
// modules/dashboard/View.php
class View {
  public function render($data) {
    ?>
    <div id="chart-app"></div>
    
    <script type="module">
      import { render } from 'preact';
      import { html } from 'htm/preact';
      import Chart from '/components/Chart.js';
      
      const phpData = <?= json_encode($data) ?>;
      
      render(html`<${Chart} data=${phpData} />`,
             document.getElementById('chart-app'));
    </script>
    <?php
  }
}
```

```javascript
// common/components/Chart.js
import { html } from 'https://esm.sh/htm@3.1.1/preact?external=preact';

export default function Chart({ data }) {
  return html`
    <div class="chart">
      ${data.map(item => html`
        <div class="bar" style="height: ${item.value}%">
          ${item.label}
        </div>
      `)}
    </div>
  `;
}
```

**Pros:**
- ✅ Reusable across modules
- ✅ Organized codebase
- ✅ Easier to test

**Cons:**
- ❌ Need component serving endpoint
- ❌ Slightly more complex

### Pattern 3: **Hydration** (Progressive Enhancement)

```php
<?php
// Server renders initial state
class View {
  public function render($data) {
    ?>
    <!-- Server-rendered content -->
    <div id="todo-app">
      <?php foreach($data['todos'] as $todo): ?>
        <div class="todo">
          <input type="checkbox" <?= $todo->completed ? 'checked' : '' ?>>
          <span><?= htmlspecialchars($todo->text) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
    
    <!-- Island hydrates existing content -->
    <script type="module">
      import { hydrate } from 'preact';
      import TodoApp from '/components/TodoApp.js';
      
      const initialData = <?= json_encode($data['todos']) ?>;
      
      hydrate(html`<${TodoApp} todos=${initialData} />`,
              document.getElementById('todo-app'));
    </script>
    <?php
  }
}
```

**Pros:**
- ✅ Works without JS
- ✅ No content flash
- ✅ Best performance

**Cons:**
- ❌ More complex setup
- ❌ Must match server/client HTML

---

## Best Practices

### 1. **Data Passing**

#### ✅ **Good: JSON in Script Tag**
```php
<script type="application/json" id="app-data">
  <?= json_encode($data, JSON_PRETTY_PRINT) ?>
</script>

<script type="module">
  const data = JSON.parse(
    document.getElementById('app-data').textContent
  );
</script>
```

#### ❌ **Bad: Inline in JS**
```php
<script>
  // XSS vulnerability!
  const data = <?= json_encode($data) ?>;
</script>
```

### 2. **Error Handling**

```javascript
// Wrap islands in error boundaries
function Island() {
  try {
    return html`<${MyComponent} />`;
  } catch (error) {
    console.error('Island failed:', error);
    return html`
      <div class="error">
        Something went wrong. Please refresh.
      </div>
    `;
  }
}
```

### 3. **Loading States**

```php
<!-- Show content immediately -->
<div id="chart-app">
  <div class="loading">Loading chart...</div>
</div>

<script type="module">
  // Island replaces loading state
  render(html`<${Chart} />`, 
         document.getElementById('chart-app'));
</script>
```

### 4. **Accessibility**

```javascript
// Islands should be accessible
function SearchIsland() {
  return html`
    <div role="search">
      <label for="search-input">Search:</label>
      <input 
        id="search-input"
        type="search"
        aria-label="Search products"
      />
    </div>
  `;
}
```

### 5. **Performance**

```javascript
// Lazy load heavy islands
<div id="chart-app"></div>

<script type="module">
  // Load only when visible
  const observer = new IntersectionObserver((entries) => {
    if (entries[0].isIntersecting) {
      import('./Chart.js').then(module => {
        render(html`<${module.default} />`, 
               document.getElementById('chart-app'));
      });
      observer.disconnect();
    }
  });
  
  observer.observe(document.getElementById('chart-app'));
</script>
```

---

## Real-World Examples

### Example 1: **E-Commerce Product Page**

```
┌────────────────────────────────────┐
│  Product Page (95% PHP, 5% React)  │
├────────────────────────────────────┤
│                                    │
│  Header (PHP)                      │
│  Navigation (PHP)                  │
│                                    │
│  ┌──────────────────────────────┐ │
│  │ Product Images (PHP)         │ │
│  │ - SEO friendly               │ │
│  │ - Fast load                  │ │
│  └──────────────────────────────┘ │
│                                    │
│  Product Details (PHP)             │
│  - Title, description, price       │
│  - All indexed by Google           │
│                                    │
│  ┌──────────────────────────────┐ │
│  │ React Island: Add to Cart    │ │
│  │ - Size selector              │ │
│  │ - Quantity picker            │ │
│  │ - Live price update          │ │
│  └──────────────────────────────┘ │
│                                    │
│  Reviews (PHP - server rendered)   │
│                                    │
│  ┌──────────────────────────────┐ │
│  │ React Island: Review Filter  │ │
│  │ - Star rating filter         │ │
│  │ - Sort by date/helpful       │ │
│  └──────────────────────────────┘ │
│                                    │
│  Related Products (PHP)            │
│  Footer (PHP)                      │
└────────────────────────────────────┘
```

### Example 2: **Dashboard**

```
┌────────────────────────────────────┐
│  Dashboard (60% PHP, 40% React)    │
├────────────────────────────────────┤
│                                    │
│  Sidebar (PHP)                     │
│  Top Bar (PHP)                     │
│                                    │
│  ┌──────────────────────────────┐ │
│  │ React Island: Live Stats     │ │
│  │ - Updates every 5s           │ │
│  │ - WebSocket connection       │ │
│  └──────────────────────────────┘ │
│                                    │
│  ┌──────────────────────────────┐ │
│  │ React Island: Interactive    │ │
│  │              Chart            │ │
│  │ - Date range picker          │ │
│  │ - Drill-down                 │ │
│  └──────────────────────────────┘ │
│                                    │
│  ┌──────────────────────────────┐ │
│  │ React Island: Data Table     │ │
│  │ - Sort, filter, paginate     │ │
│  │ - Inline edit                │ │
│  └──────────────────────────────┘ │
└────────────────────────────────────┘
```

---

## Comparison with Other Architectures

| Approach | Initial Load | Interactivity | SEO | Complexity | Build Step |
|----------|-------------|---------------|-----|------------|------------|
| **Pure PHP** | ⚡⚡⚡ Fast | ❌ Poor | ✅ Great | ⭐ Simple | ❌ None |
| **Full SPA** | 🐌 Slow | ✅ Rich | ⚠️ Tricky | ⭐⭐⭐ Complex | ✅ Required |
| **SSR React** | ⚡⚡ Good | ✅ Rich | ✅ Great | ⭐⭐⭐⭐ Very Complex | ✅ Required |
| **Islands (upMVC)** | ⚡⚡⚡ Fast | ✅ Rich | ✅ Great | ⭐⭐ Moderate | ❌ None |

---

## Migration Guide

### From Pure PHP to Islands

```php
// BEFORE: Pure PHP (no interactivity)
class View {
  public function render($todos) {
    foreach($todos as $todo) {
      echo "<div>{$todo->text}</div>";
    }
  }
}

// AFTER: Add React Island
class View {
  public function render($todos) {
    ?>
    <!-- Server-rendered fallback -->
    <div id="todo-app">
      <?php foreach($todos as $todo): ?>
        <div><?= $todo->text ?></div>
      <?php endforeach; ?>
    </div>
    
    <!-- Island adds interactivity -->
    <script type="module">
      import { render } from 'preact';
      import TodoApp from '/components/TodoApp.js';
      
      render(html`<${TodoApp} todos=${<?= json_encode($todos) ?>} />`,
             document.getElementById('todo-app'));
    </script>
    <?php
  }
}
```

### From Full SPA to Islands

1. **Keep React components** - They still work!
2. **Move routing to PHP** - Use upMVC router
3. **Server-render layout** - PHP does the shell
4. **Mount islands** - Use `render()` instead of full app

---

## Conclusion

The **Islands Architecture** is the perfect balance between **simplicity and interactivity**. In upMVC:

✅ **Use PHP** for routing, auth, data, and static content  
✅ **Use React Islands** for interactive features  
✅ **No build step** means faster development  
✅ **Progressive enhancement** means better UX  

**This is modern web development done right!** 🚀

---

## Further Reading

- [Islands Architecture by Jason Miller](https://jasonformat.com/islands-architecture/)
- [The Primeagen - Islands vs SPA](https://www.youtube.com/watch?v=0tMiAHKsNzI)
- [Astro Islands Documentation](https://docs.astro.build/en/concepts/islands/)
- [upMVC React Patterns](./REACT_INTEGRATION_PATTERNS.md)
- [upMVC ReactHMR Guide](./REACTHMR_IMPLEMENTATION.md)

---

**Next Steps:**

1. [Create a Search Island →](./examples/SEARCH_ISLAND.md)
2. [Create a Chart Island →](./examples/CHART_ISLAND.md)
3. [Create a Form Island →](./examples/FORM_ISLAND.md)
4. [State Management →](./STATE_MANAGEMENT.md)
5. [Component Library →](./COMPONENT_LIBRARY.md)
