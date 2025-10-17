# React Integration Patterns - Quick Reference

## 🎯 Which Pattern Should I Use?

```
Need simple button/widget?
    → Pattern 1 (CDN Components)

Want modern JS WITHOUT build hassle?
    → Pattern 4 (ES Modules - reactnb)

Need full React in part of page?
    → Pattern 2 (Built Embedded - reactb)

Building complete SPA?
    → Pattern 3 (Built Full Page - reactcrud)
```

---

## 📊 Quick Comparison

| | Pattern 1<br>`/react` | Pattern 2<br>`/reactb` | Pattern 3<br>`/reactcrud` | Pattern 4<br>`/reactnb` |
|---|---|---|---|---|
| **Build** | No | Yes | Yes | No |
| **JSX** | No | Yes | Yes | HTM (similar) |
| **Setup Time** | 5 min | 30 min | 60 min | 10 min |
| **Complexity** | ⭐ | ⭐⭐ | ⭐⭐⭐ | ⭐⭐ |
| **Best For** | Widgets | Sections | Full SPA | Rapid Dev |

---

## 🚀 Getting Started

### Pattern 1 - Add React Widget
```html
<!-- Load React from CDN -->
<script src="https://unpkg.com/react@18/umd/react.development.js"></script>
<script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>

<!-- Container -->
<div id="app"></div>

<!-- Your component (no JSX) -->
<script>
const e = React.createElement;
ReactDOM.createRoot(document.getElementById('app'))
  .render(e('h1', null, 'Hello React!'));
</script>
```

### Pattern 4 - Modern JS No Build
```html
<!-- Import Map -->
<script type="importmap">
{
  "imports": {
    "preact": "https://esm.sh/preact@10",
    "htm/preact": "https://esm.sh/htm@3/preact?external=preact"
  }
}
</script>

<!-- Component with HTM (JSX-like) -->
<script type="module">
import { render } from 'preact';
import { html } from 'htm/preact';

function App() {
  return html`<h1>Hello Preact!</h1>`;
}

render(html`<${App} />`, document.getElementById('app'));
</script>
```

### Pattern 2/3 - React Build
```bash
# 1. Create React app
cd modules/reactb/etc
npx create-react-app react-app

# 2. Build
cd react-app
npm run build

# 3. Copy build/ to modules/reactb/etc/build/

# 4. Create PHP routes for assets
# See REACT_INTEGRATION_PATTERNS.md for details
```

---

## 🔑 Key Code Patterns

### Serving Assets via PHP Routes

```php
// Controller.php
public function mainjs()
{
    require_once THIS_DIR . "/modules/reactb/etc/build/static/js/main.js";
}

// routes/Routes.php
$router->addRoute("/reactb/js", Controller::class, "mainjs");
```

### Using Import Maps (Pattern 4)

```html
<script type="importmap">
{
  "imports": {
    "preact": "https://esm.sh/preact@10.23.1",
    "preact/hooks": "https://esm.sh/preact@10.23.1/hooks",
    "htm/preact": "https://esm.sh/htm@3.1.1/preact?external=preact"
  }
}
</script>
```

### HTM Syntax (Pattern 4)

```javascript
import { html } from 'htm/preact';
import { useState } from 'preact/hooks';

function Counter() {
    const [count, setCount] = useState(0);
    
    return html`
        <div>
            <button onClick=${() => setCount(count + 1)}>
                Count: ${count}
            </button>
        </div>
    `;
}
```

---

## 📋 Checklist

### Pattern 1 Setup
- [ ] Add React CDN scripts to View.php
- [ ] Create component.js file
- [ ] Add PHP route to serve component.js
- [ ] Test at `/react`

### Pattern 4 Setup
- [ ] Add import map to View.php
- [ ] Write components inline with HTM
- [ ] Test at `/reactnb`
- [ ] No build needed!

### Pattern 2/3 Setup
- [ ] Create React app with `create-react-app`
- [ ] Build with `npm run build`
- [ ] Copy build folder to module
- [ ] Create PHP routes for each asset
- [ ] Update View.php with asset paths
- [ ] Test routes

---

## 🎓 Learning Order

```
1. Start → Pattern 1 (simplest)
2. Try   → Pattern 4 (modern, no build)
3. Learn → Pattern 2 (embedded build)
4. Build → Pattern 3 (full SPA)
```

---

## 🔗 Full Documentation

- **[Complete Guide](REACT_INTEGRATION_PATTERNS.md)** - Full details, examples, workflows
- **[Analysis Summary](REACT_PATTERNS_SUMMARY.md)** - Technical analysis
- **[Module Philosophy](MODULE_PHILOSOPHY.md)** - Why these are reference implementations

---

## 💡 Pro Tips

### Pattern 1
- Use for: Login forms, simple counters, like buttons
- Avoid: Complex state management, routing

### Pattern 4
- Use for: Rapid prototyping, comparing frameworks
- Avoid: Large production apps (no tree shaking)

### Pattern 2
- Use for: Dashboard widgets, admin panels
- Keep: PHP layout, navigation, footer

### Pattern 3
- Use for: Full CRUD apps, client-side routing
- Keep: PHP only for API/auth

---

## 🚨 Common Mistakes

### Pattern 1
❌ Trying to use JSX → Use `React.createElement` instead  
✅ Keep components simple

### Pattern 4
❌ Forgetting `type="module"` → Scripts won't load  
✅ Always use `<script type="module">`

### Pattern 2/3
❌ Forgetting to update paths after build → 404 errors  
✅ Check build/index.html for correct asset paths

---

## 📊 Performance

| Pattern | Initial Load | Development | Production |
|---------|-------------|-------------|------------|
| Pattern 1 | Fast | Instant | Good |
| Pattern 2 | Medium | Rebuild | Optimized |
| Pattern 3 | Medium | Rebuild | Optimized |
| Pattern 4 | Fast | Instant | Good* |

*Pattern 4: No bundle optimization, but modern browsers handle ES modules efficiently for small-medium apps.

---

## 🎯 Decision Tree

```
Do you need to build a complex SPA?
├─ Yes → Do you want React Router, Redux, etc?
│        ├─ Yes → Pattern 3 (Full SPA)
│        └─ No  → Pattern 2 (Embedded)
│
└─ No → Do you want a build step?
         ├─ Yes → Pattern 2 (Embedded)
         └─ No  → Do you need JSX-like syntax?
                  ├─ Yes → Pattern 4 (ES Modules + HTM)
                  └─ No  → Pattern 1 (CDN Simple)
```

---

## ✅ Quick Commands

```bash
# Visit patterns
/react      # Pattern 1: CDN Components
/reactb     # Pattern 2: Built (Embedded)
/crud       # Pattern 3: Built (Full Page)
/reactnb    # Pattern 4: ES Modules (No Build)

# Build Pattern 2/3
cd modules/reactb/etc/react-app
npm install
npm run build

# Delete unused patterns
rm -rf modules/react modules/reactb modules/reactcrud
# Keep only what you use!
```

---

**Remember:** These are **examples**, not requirements. Study, choose one, delete the rest, build your way! 🚀
