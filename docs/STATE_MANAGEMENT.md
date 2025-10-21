# State Management for Islands

## Overview

When using **multiple islands on the same page**, you often need to **share state** between them. This guide shows lightweight state management patterns without heavy libraries like Redux.

---

## Pattern 1: Event Bus (Simplest)

### Create Event Bus

```javascript
// common/assets/js/eventBus.js

class EventBus {
    constructor() {
        this.events = {};
    }

    on(event, callback) {
        if (!this.events[event]) {
            this.events[event] = [];
        }
        this.events[event].push(callback);
        
        // Return unsubscribe function
        return () => {
            this.events[event] = this.events[event].filter(cb => cb !== callback);
        };
    }

    emit(event, data) {
        if (this.events[event]) {
            this.events[event].forEach(callback => callback(data));
        }
    }

    off(event) {
        delete this.events[event];
    }
}

// Global instance
window.eventBus = new EventBus();

export default window.eventBus;
```

### Usage in Islands

```javascript
// Island 1: Shopping Cart
function CartIsland() {
    const [itemCount, setItemCount] = useState(0);

    useEffect(() => {
        // Listen for cart updates
        const unsubscribe = window.eventBus.on('cart:updated', (count) => {
            setItemCount(count);
        });

        return unsubscribe; // Cleanup on unmount
    }, []);

    return html`
        <div class="cart">
            🛒 Cart (${itemCount})
        </div>
    `;
}

// Island 2: Product List
function ProductIsland() {
    const addToCart = () => {
        // Emit event
        window.eventBus.emit('cart:updated', 5);
    };

    return html`
        <button onClick=${addToCart}>
            Add to Cart
        </button>
    `;
}
```

---

## Pattern 2: Shared State with Preact Signals

### Install Signals

```html
<script type="importmap">
{
    "imports": {
        "@preact/signals": "https://esm.sh/@preact/signals@1.3.0?external=preact"
    }
}
</script>
```

### Create Shared Store

```javascript
// common/assets/js/store.js
import { signal, computed } from '@preact/signals';

// Create signals (reactive state)
export const cart = signal([]);
export const user = signal(null);

// Computed values
export const cartTotal = computed(() => {
    return cart.value.reduce((sum, item) => sum + item.price * item.quantity, 0);
});

export const cartItemCount = computed(() => {
    return cart.value.reduce((sum, item) => sum + item.quantity, 0);
});

// Actions
export function addToCart(product) {
    const existing = cart.value.find(item => item.id === product.id);
    
    if (existing) {
        cart.value = cart.value.map(item =>
            item.id === product.id
                ? { ...item, quantity: item.quantity + 1 }
                : item
        );
    } else {
        cart.value = [...cart.value, { ...product, quantity: 1 }];
    }
}

export function removeFromCart(productId) {
    cart.value = cart.value.filter(item => item.id !== productId);
}

export function setUser(userData) {
    user.value = userData;
}
```

### Usage in Islands

```javascript
import { cart, cartItemCount, addToCart } from '/common/assets/js/store.js';

// Island 1: Cart Badge
function CartBadge() {
    // Automatically re-renders when cartItemCount changes!
    return html`
        <div class="cart-badge">
            🛒 ${cartItemCount.value} items
        </div>
    `;
}

// Island 2: Product Card
function ProductCard({ product }) {
    const handleAdd = () => {
        addToCart(product);
        // Cart badge updates automatically!
    };

    return html`
        <div class="product">
            <h3>${product.name}</h3>
            <p>$${product.price}</p>
            <button onClick=${handleAdd}>Add to Cart</button>
        </div>
    `;
}

// Island 3: Cart Sidebar
function CartSidebar() {
    return html`
        <div class="cart-sidebar">
            <h2>Your Cart</h2>
            ${cart.value.map(item => html`
                <div key=${item.id}>
                    ${item.name} × ${item.quantity}
                </div>
            `)}
        </div>
    `;
}
```

---

## Pattern 3: LocalStorage Persistence

### Create Persistent Store

```javascript
// common/assets/js/persistentStore.js
import { signal, effect } from '@preact/signals';

function createPersistedSignal(key, initialValue) {
    // Load from localStorage
    const stored = localStorage.getItem(key);
    const sig = signal(stored ? JSON.parse(stored) : initialValue);

    // Save to localStorage on change
    effect(() => {
        localStorage.setItem(key, JSON.stringify(sig.value));
    });

    return sig;
}

// Persisted state
export const cart = createPersistedSignal('cart', []);
export const favorites = createPersistedSignal('favorites', []);
export const recentSearches = createPersistedSignal('searches', []);
```

### Usage

```javascript
import { cart } from '/common/assets/js/persistentStore.js';

function CartIsland() {
    // State persists across page reloads!
    return html`
        <div>
            ${cart.value.length} items in cart
        </div>
    `;
}
```

---

## Pattern 4: URL State (For Filters/Pagination)

### Create URL State Hook

```javascript
function useURLState(key, defaultValue) {
    const url = new URL(window.location);
    const [value, setValue] = useState(
        url.searchParams.get(key) || defaultValue
    );

    const updateValue = (newValue) => {
        setValue(newValue);
        
        // Update URL
        const url = new URL(window.location);
        if (newValue) {
            url.searchParams.set(key, newValue);
        } else {
            url.searchParams.delete(key);
        }
        window.history.pushState({}, '', url);
    };

    return [value, updateValue];
}
```

### Usage

```javascript
function ProductFilter() {
    const [category, setCategory] = useURLState('category', 'all');
    const [sort, setSort] = useURLState('sort', 'name');

    return html`
        <div>
            <select value=${category} onChange=${(e) => setCategory(e.target.value)}>
                <option value="all">All</option>
                <option value="electronics">Electronics</option>
            </select>
            
            <select value=${sort} onChange=${(e) => setSort(e.target.value)}>
                <option value="name">Name</option>
                <option value="price">Price</option>
            </select>
        </div>
    `;
}
```

**Result:** State is in URL → shareable, bookmarkable!

---

## Pattern 5: Backend State (API-based)

### Create API Store

```javascript
// common/assets/js/apiStore.js
import { signal } from '@preact/signals';

export const products = signal([]);
export const loading = signal(false);
export const error = signal(null);

export async function fetchProducts() {
    loading.value = true;
    error.value = null;

    try {
        const res = await fetch('/api/products');
        const data = await res.json();
        products.value = data.products;
    } catch (err) {
        error.value = err.message;
    } finally {
        loading.value = false;
    }
}

export async function addProduct(product) {
    const res = await fetch('/api/products', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(product)
    });
    
    if (res.ok) {
        await fetchProducts(); // Refresh list
    }
}
```

### Usage

```javascript
import { products, loading, fetchProducts } from '/common/assets/js/apiStore.js';

function ProductList() {
    useEffect(() => {
        fetchProducts();
    }, []);

    if (loading.value) {
        return html`<div>Loading...</div>`;
    }

    return html`
        <div>
            ${products.value.map(product => html`
                <div key=${product.id}>${product.name}</div>
            `)}
        </div>
    `;
}
```

---

## Comparison

| Pattern | Use Case | Complexity | Persistence |
|---------|----------|------------|-------------|
| **Event Bus** | Simple communication | ⭐ Easy | ❌ No |
| **Preact Signals** | Reactive state | ⭐⭐ Medium | ❌ No |
| **LocalStorage** | User preferences | ⭐⭐ Medium | ✅ Yes |
| **URL State** | Filters, pagination | ⭐⭐ Medium | ✅ Yes (shareable) |
| **API State** | Server data | ⭐⭐⭐ Advanced | ✅ Yes (backend) |

---

## Complete Example: Shopping Cart

```javascript
// store.js
import { signal, computed } from '@preact/signals';

// State
export const cart = signal(
    JSON.parse(localStorage.getItem('cart') || '[]')
);

// Computed
export const cartTotal = computed(() =>
    cart.value.reduce((sum, item) => sum + item.price * item.quantity, 0)
);

export const cartCount = computed(() =>
    cart.value.reduce((sum, item) => sum + item.quantity, 0)
);

// Actions
export function addToCart(product) {
    const existing = cart.value.find(item => item.id === product.id);
    
    if (existing) {
        cart.value = cart.value.map(item =>
            item.id === product.id
                ? { ...item, quantity: item.quantity + 1 }
                : item
        );
    } else {
        cart.value = [...cart.value, { ...product, quantity: 1 }];
    }
    
    // Persist
    localStorage.setItem('cart', JSON.stringify(cart.value));
    
    // Notify
    window.eventBus.emit('cart:updated', cartCount.value);
}

export function removeFromCart(id) {
    cart.value = cart.value.filter(item => item.id !== id);
    localStorage.setItem('cart', JSON.stringify(cart.value));
    window.eventBus.emit('cart:updated', cartCount.value);
}
```

```javascript
// Island 1: Product Card
import { addToCart } from '/store.js';

function ProductCard({ product }) {
    return html`
        <div class="product">
            <img src=${product.image} />
            <h3>${product.name}</h3>
            <p>$${product.price}</p>
            <button onClick=${() => addToCart(product)}>
                Add to Cart
            </button>
        </div>
    `;
}
```

```javascript
// Island 2: Cart Badge
import { cartCount } from '/store.js';

function CartBadge() {
    return html`
        <div class="badge">
            🛒 ${cartCount.value}
        </div>
    `;
}
```

```javascript
// Island 3: Cart Sidebar
import { cart, cartTotal, removeFromCart } from '/store.js';

function CartSidebar() {
    return html`
        <div class="sidebar">
            <h2>Cart</h2>
            ${cart.value.map(item => html`
                <div key=${item.id} class="cart-item">
                    <span>${item.name} × ${item.quantity}</span>
                    <span>$${item.price * item.quantity}</span>
                    <button onClick=${() => removeFromCart(item.id)}>✕</button>
                </div>
            `)}
            <div class="total">
                Total: $${cartTotal.value}
            </div>
        </div>
    `;
}
```

---

## Best Practices

✅ **Keep state minimal** - Only share what's necessary  
✅ **Use the simplest pattern** - Don't over-engineer  
✅ **Persist important state** - LocalStorage or backend  
✅ **Clean up listeners** - Prevent memory leaks  
✅ **Type your state** - Use JSDoc for clarity  

---

**Next:** [Component Library →](./COMPONENT_LIBRARY.md)
