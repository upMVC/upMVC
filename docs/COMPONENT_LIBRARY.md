# Component Library Structure

## Overview

A **Component Library** provides reusable React islands that can be used across different modules. This creates consistency, reduces duplication, and speeds up development.

---

## Directory Structure

```
common/
├── assets/
│   ├── js/
│   │   ├── store.js              # Shared state
│   │   ├── eventBus.js           # Event system
│   │   └── utils.js              # Helper functions
│   └── css/
│       └── components.css         # Component styles
├── components/
│   ├── README.md                  # Component docs
│   ├── Button.js                  # Button component
│   ├── Card.js                    # Card component
│   ├── Modal.js                   # Modal component
│   ├── Dropdown.js                # Dropdown component
│   ├── Table.js                   # Data table
│   ├── Chart.js                   # Charts
│   ├── Form/
│   │   ├── Input.js              # Form input
│   │   ├── Select.js             # Form select
│   │   ├── Textarea.js           # Form textarea
│   │   └── Checkbox.js           # Form checkbox
│   └── Layout/
│       ├── Header.js             # Header component
│       ├── Footer.js             # Footer component
│       └── Sidebar.js            # Sidebar component
└── Bmvc/
    └── BaseView.php               # PHP base view
```

---

## Component Examples

### 1. Button Component

```javascript
// common/components/Button.js

/**
 * Button Component
 * 
 * @param {Object} props
 * @param {string} props.variant - 'primary', 'secondary', 'danger'
 * @param {string} props.size - 'small', 'medium', 'large'
 * @param {boolean} props.disabled - Disabled state
 * @param {Function} props.onClick - Click handler
 * @param {any} props.children - Button content
 */

import { html } from 'https://esm.sh/htm@3.1.1/preact?external=preact';

export default function Button({
    variant = 'primary',
    size = 'medium',
    disabled = false,
    loading = false,
    onClick,
    children
}) {
    const baseClass = 'btn';
    const variantClass = `btn-${variant}`;
    const sizeClass = `btn-${size}`;
    const className = `${baseClass} ${variantClass} ${sizeClass}`;

    return html`
        <button
            class=${className}
            disabled=${disabled || loading}
            onClick=${onClick}
        >
            ${loading ? '⏳ ' : ''}
            ${children}
        </button>
    `;
}

// Usage:
// import Button from '/common/components/Button.js';
// 
// html`
//   <${Button} variant="primary" onClick=${handleClick}>
//     Click Me
//   <//>
// `
```

### 2. Card Component

```javascript
// common/components/Card.js

/**
 * Card Component
 * 
 * @param {Object} props
 * @param {string} props.title - Card title
 * @param {string} props.subtitle - Card subtitle
 * @param {any} props.children - Card content
 * @param {Function} props.onClose - Optional close handler
 */

import { html } from 'https://esm.sh/htm@3.1.1/preact?external=preact';

export default function Card({ title, subtitle, children, onClose }) {
    return html`
        <div class="card">
            ${(title || onClose) && html`
                <div class="card-header">
                    <div>
                        ${title && html`<h3 class="card-title">${title}</h3>`}
                        ${subtitle && html`<p class="card-subtitle">${subtitle}</p>`}
                    </div>
                    ${onClose && html`
                        <button class="card-close" onClick=${onClose}>✕</button>
                    `}
                </div>
            `}
            <div class="card-body">
                ${children}
            </div>
        </div>
    `;
}
```

### 3. Modal Component

```javascript
// common/components/Modal.js

/**
 * Modal Component
 * 
 * @param {Object} props
 * @param {boolean} props.open - Modal open state
 * @param {Function} props.onClose - Close handler
 * @param {string} props.title - Modal title
 * @param {any} props.children - Modal content
 */

import { useEffect } from 'https://esm.sh/preact@10.23.1/hooks';
import { html } from 'https://esm.sh/htm@3.1.1/preact?external=preact';

export default function Modal({ open, onClose, title, children }) {
    useEffect(() => {
        // Close on Escape key
        const handleEscape = (e) => {
            if (e.key === 'Escape' && open) {
                onClose();
            }
        };

        document.addEventListener('keydown', handleEscape);
        return () => document.removeEventListener('keydown', handleEscape);
    }, [open, onClose]);

    useEffect(() => {
        // Prevent body scroll when modal is open
        if (open) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }

        return () => {
            document.body.style.overflow = '';
        };
    }, [open]);

    if (!open) return null;

    return html`
        <div class="modal-overlay" onClick=${onClose}>
            <div class="modal" onClick=${(e) => e.stopPropagation()}>
                <div class="modal-header">
                    <h2>${title}</h2>
                    <button class="modal-close" onClick=${onClose}>✕</button>
                </div>
                <div class="modal-body">
                    ${children}
                </div>
            </div>
        </div>
    `;
}
```

### 4. Dropdown Component

```javascript
// common/components/Dropdown.js

/**
 * Dropdown Component
 * 
 * @param {Object} props
 * @param {string} props.label - Dropdown label
 * @param {Array} props.options - Array of options
 * @param {Function} props.onChange - Change handler
 */

import { useState, useEffect, useRef } from 'https://esm.sh/preact@10.23.1/hooks';
import { html } from 'https://esm.sh/htm@3.1.1/preact?external=preact';

export default function Dropdown({ label, options, value, onChange }) {
    const [open, setOpen] = useState(false);
    const dropdownRef = useRef(null);

    useEffect(() => {
        const handleClickOutside = (e) => {
            if (dropdownRef.current && !dropdownRef.current.contains(e.target)) {
                setOpen(false);
            }
        };

        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    const selectedOption = options.find(opt => opt.value === value);

    return html`
        <div class="dropdown" ref=${dropdownRef}>
            <button 
                class="dropdown-toggle"
                onClick=${() => setOpen(!open)}
            >
                ${selectedOption ? selectedOption.label : label}
                <span class="dropdown-arrow">${open ? '▲' : '▼'}</span>
            </button>

            ${open && html`
                <div class="dropdown-menu">
                    ${options.map(option => html`
                        <div
                            key=${option.value}
                            class=${`dropdown-item ${option.value === value ? 'active' : ''}`}
                            onClick=${() => {
                                onChange(option.value);
                                setOpen(false);
                            }}
                        >
                            ${option.label}
                        </div>
                    `)}
                </div>
            `}
        </div>
    `;
}
```

### 5. Data Table Component

```javascript
// common/components/Table.js

/**
 * Data Table Component
 * 
 * @param {Object} props
 * @param {Array} props.columns - Column definitions
 * @param {Array} props.data - Table data
 * @param {Function} props.onSort - Sort handler
 */

import { useState } from 'https://esm.sh/preact@10.23.1/hooks';
import { html } from 'https://esm.sh/htm@3.1.1/preact?external=preact';

export default function Table({ columns, data, onRowClick }) {
    const [sortColumn, setSortColumn] = useState(null);
    const [sortDirection, setSortDirection] = useState('asc');

    const handleSort = (column) => {
        if (sortColumn === column) {
            setSortDirection(sortDirection === 'asc' ? 'desc' : 'asc');
        } else {
            setSortColumn(column);
            setSortDirection('asc');
        }
    };

    const sortedData = [...data].sort((a, b) => {
        if (!sortColumn) return 0;

        const aVal = a[sortColumn];
        const bVal = b[sortColumn];

        if (sortDirection === 'asc') {
            return aVal > bVal ? 1 : -1;
        } else {
            return aVal < bVal ? 1 : -1;
        }
    });

    return html`
        <table class="table">
            <thead>
                <tr>
                    ${columns.map(col => html`
                        <th
                            key=${col.key}
                            onClick=${() => handleSort(col.key)}
                            class=${sortColumn === col.key ? 'sorted' : ''}
                        >
                            ${col.label}
                            ${sortColumn === col.key && html`
                                <span>${sortDirection === 'asc' ? ' ↑' : ' ↓'}</span>
                            `}
                        </th>
                    `)}
                </tr>
            </thead>
            <tbody>
                ${sortedData.map(row => html`
                    <tr
                        key=${row.id}
                        onClick=${() => onRowClick && onRowClick(row)}
                        class=${onRowClick ? 'clickable' : ''}
                    >
                        ${columns.map(col => html`
                            <td key=${col.key}>
                                ${col.render ? col.render(row[col.key], row) : row[col.key]}
                            </td>
                        `)}
                    </tr>
                `)}
            </tbody>
        </table>
    `;
}

// Usage:
// const columns = [
//   { key: 'id', label: 'ID' },
//   { key: 'name', label: 'Name' },
//   { key: 'price', label: 'Price', render: (val) => `$${val}` }
// ];
// 
// html`<${Table} columns=${columns} data=${products} />`
```

---

## Component Stylesheet

```css
/* common/assets/css/components.css */

/* Button */
.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 1em;
    transition: all 0.2s;
}

.btn-primary {
    background: #667eea;
    color: white;
}

.btn-primary:hover {
    background: #5568d3;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-danger {
    background: #dc3545;
    color: white;
}

.btn-small {
    padding: 6px 12px;
    font-size: 0.9em;
}

.btn-large {
    padding: 14px 28px;
    font-size: 1.1em;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Card */
.card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    overflow: hidden;
}

.card-header {
    padding: 20px;
    border-bottom: 1px solid #e0e0e0;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.card-title {
    margin: 0;
    color: #333;
}

.card-subtitle {
    margin: 5px 0 0 0;
    color: #666;
    font-size: 0.9em;
}

.card-close {
    background: none;
    border: none;
    font-size: 1.5em;
    cursor: pointer;
    color: #999;
}

.card-body {
    padding: 20px;
}

/* Modal */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal {
    background: white;
    border-radius: 12px;
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow: auto;
    box-shadow: 0 8px 32px rgba(0,0,0,0.3);
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid #e0e0e0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5em;
    cursor: pointer;
}

.modal-body {
    padding: 20px;
}

/* Dropdown */
.dropdown {
    position: relative;
    display: inline-block;
}

.dropdown-toggle {
    padding: 10px 15px;
    background: white;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
}

.dropdown-arrow {
    font-size: 0.8em;
}

.dropdown-menu {
    position: absolute;
    top: 100%;
    left: 0;
    margin-top: 5px;
    background: white;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    min-width: 200px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    z-index: 100;
}

.dropdown-item {
    padding: 10px 15px;
    cursor: pointer;
    transition: background 0.2s;
}

.dropdown-item:hover {
    background: #f5f5f5;
}

.dropdown-item.active {
    background: #667eea;
    color: white;
}

/* Table */
.table {
    width: 100%;
    border-collapse: collapse;
}

.table th {
    background: #f5f5f5;
    padding: 12px;
    text-align: left;
    font-weight: 600;
    cursor: pointer;
    user-select: none;
}

.table th:hover {
    background: #e8e8e8;
}

.table th.sorted {
    background: #667eea;
    color: white;
}

.table td {
    padding: 12px;
    border-bottom: 1px solid #e0e0e0;
}

.table tr.clickable {
    cursor: pointer;
}

.table tr.clickable:hover {
    background: #f9f9f9;
}
```

---

## Using Components in Modules

```php
<?php
// modules/products/View.php

class View {
    public function render($data) {
        ?>
        <!-- Include component CSS -->
        <link rel="stylesheet" href="<?= BASE_URL; ?>/common/assets/css/components.css">
        
        <div id="app"></div>

        <script type="module">
            import { render } from 'preact';
            import { html } from 'htm/preact';
            
            // Import components
            import Button from '<?= BASE_URL; ?>/common/components/Button.js';
            import Card from '<?= BASE_URL; ?>/common/components/Card.js';
            import Modal from '<?= BASE_URL; ?>/common/components/Modal.js';
            import Table from '<?= BASE_URL; ?>/common/components/Table.js';

            function App() {
                const [modalOpen, setModalOpen] = useState(false);

                const columns = [
                    { key: 'id', label: 'ID' },
                    { key: 'name', label: 'Product' },
                    { key: 'price', label: 'Price', render: (val) => `$${val}` }
                ];

                const products = <?= json_encode($data['products']); ?>;

                return html`
                    <${Card} title="Products">
                        <${Button} 
                            variant="primary"
                            onClick=${() => setModalOpen(true)}
                        >
                            Add Product
                        <//>

                        <${Table}
                            columns=${columns}
                            data=${products}
                            onRowClick=${(row) => alert(row.name)}
                        />
                    <//>

                    <${Modal}
                        open=${modalOpen}
                        onClose=${() => setModalOpen(false)}
                        title="Add Product"
                    >
                        <form>
                            <input placeholder="Product name" />
                            <${Button} type="submit">Save<//>
                        </form>
                    <//>
                `;
            }

            render(html`<${App} />`, document.getElementById('app'));
        </script>
        <?php
    }
}
```

---

## Component Documentation

Create a **README.md** in the components directory:

```markdown
# Component Library

## Available Components

- [Button](#button)
- [Card](#card)
- [Modal](#modal)
- [Dropdown](#dropdown)
- [Table](#table)

### Button

**Props:**
- `variant`: 'primary' | 'secondary' | 'danger'
- `size`: 'small' | 'medium' | 'large'
- `disabled`: boolean
- `loading`: boolean
- `onClick`: function

**Example:**
\`\`\`javascript
import Button from '/common/components/Button.js';

html`
  <${Button} variant="primary" onClick=${handleClick}>
    Click Me
  <//>
`
\`\`\`

### Card

**Props:**
- `title`: string
- `subtitle`: string
- `onClose`: function

**Example:**
\`\`\`javascript
import Card from '/common/components/Card.js';

html`
  <${Card} title="My Card" subtitle="Description">
    Card content here
  <//>
`
\`\`\`

(Continue for all components...)
```

---

## Best Practices

✅ **Export default** - Easier imports  
✅ **Document props** - Use JSDoc comments  
✅ **Style in CSS** - Keep JS logic separate  
✅ **Make components small** - Single responsibility  
✅ **Use TypeScript (optional)** - Better DX with JSDoc  
✅ **Version components** - Track breaking changes  

---

## Summary

✅ Created comprehensive Islands Architecture documentation  
✅ Built Search Island example (debouncing, keyboard nav)  
✅ Built Chart Island example (bar, line, pie charts)  
✅ Built Form Island examples (validation, multi-step)  
✅ Created State Management patterns (5 approaches)  
✅ Built Component Library structure (reusable components)  

**Your upMVC framework now has complete documentation for building modern web apps with PHP + React Islands, no build step required!** 🚀
